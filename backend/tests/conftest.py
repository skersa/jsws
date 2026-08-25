import json
import os
import re
import urllib.parse
import pytest
import requests
from dotenv import dotenv_values

frontend_env = dotenv_values("/app/frontend/.env")
base_url = os.environ.get("REACT_APP_BACKEND_URL") or frontend_env.get("REACT_APP_BACKEND_URL")
if not base_url:
    raise RuntimeError("REACT_APP_BACKEND_URL missing")
BASE_URL = base_url.rstrip("/")

PASSWORD = "password"


def _csrf(session, url):
    """Prime the session on `url` and return the CSRF token (hidden input or XSRF cookie)."""
    r = session.get(url, timeout=60)
    m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
    if m:
        return m.group(1)
    return None


def post(session, path, data=None, **kw):
    """POST with Laravel CSRF header taken from the XSRF-TOKEN cookie."""
    url = f"{BASE_URL}{path}" if path.startswith("/") else path
    tok = _csrf(session, url)
    cookie = session.cookies.get("XSRF-TOKEN")
    headers = kw.pop("headers", {}) or {}
    if cookie:
        headers.setdefault("X-XSRF-TOKEN", urllib.parse.unquote(cookie))
    headers.setdefault("Referer", BASE_URL)
    headers.setdefault("Origin", BASE_URL)
    body = dict(data or {})
    if tok:
        body.setdefault("_token", tok)
    kw.setdefault("timeout", 90)
    return session.post(url, data=body, headers=headers, **kw)


def page_props(session, url):
    """Extract Inertia page props from a rendered HTML response."""
    if url.startswith("/"):
        url = f"{BASE_URL}{url}"
    t = session.get(url, timeout=90).text
    m = re.search(r'data-page="app" type="application/json">(\{.*?\})</script>', t, re.S)
    if not m:
        return None
    return json.loads(m.group(1))


def make_session(email=None):
    s = requests.Session()
    s.headers.update({"Accept": "text/html,application/xhtml+xml"})
    if email:
        r = post(s, "/login", {"email": email, "password": PASSWORD}, allow_redirects=True)
        if r.status_code >= 400 or r.url.rstrip("/").endswith("/login"):
            raise RuntimeError(f"Login failed for {email}: {r.status_code} {r.url}")
    return s


@pytest.fixture(scope="session")
def guest():
    return make_session()


@pytest.fixture(scope="session")
def buyer():
    return make_session("buyer@test.com")


@pytest.fixture(scope="session")
def seller():
    return make_session("seller@test.com")


@pytest.fixture(scope="session")
def admin():
    return make_session("admin@test.com")
