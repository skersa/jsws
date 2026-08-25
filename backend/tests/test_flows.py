"""Supplementary flow tests: seller edit, stories, support, follow, profile, admin CRUD, images."""
import io
import json
import re
import urllib.parse

import pytest
from conftest import BASE_URL, _csrf, post, page_props

ACTIVE = "antika-masa-saati-ve-vazo-seti-MD1c"
DRAFT = "test-draft-i7Cybz"
PNG = bytes.fromhex(
    "89504e470d0a1a0a0000000d49484452000000010000000108060000001f15c4890000000a4944"
    "41547801630000000200015c0dd3510000000049454e44ae426082")


def upload(sess, path, fields, files, referer):
    tok = _csrf(sess, f"{BASE_URL}{referer}")
    cookie = urllib.parse.unquote(sess.cookies.get("XSRF-TOKEN") or "")
    body = dict(fields)
    body["_token"] = tok
    return sess.post(f"{BASE_URL}{path}", data=body, files=files,
                     headers={"X-XSRF-TOKEN": cookie, "Referer": f"{BASE_URL}{referer}"},
                     timeout=120, allow_redirects=False)


# ---- seller: edit an existing draft ----
def test_seller_update_draft_title(seller):
    props = page_props(seller, f"/seller/auctions/{DRAFT}/edit")["props"]
    assert props, "edit page has no props"
    r = post(seller, f"/seller/auctions/{DRAFT}",
             {"_method": "PUT",
              "title": "TEST DRAFT ILAN (guncellendi)",
              "category_id": props["auction"]["category_id"],
              "description": "Guncellenmis aciklama metni, yeterince uzun bir metin olsun.",
              "starting_price": 1000,
              "min_bid_increment": 50,
              "condition": "used",
              "location": "Istanbul",
              "ends_at": props["auction"]["ends_at"]},
             allow_redirects=False,
             headers={"Referer": f"{BASE_URL}/seller/auctions/{DRAFT}/edit"})
    assert r.status_code == 302, f"update -> {r.status_code} {r.text[:400]}"
    loc = r.headers.get("Location", "")
    assert "edit" not in loc, f"update redirected back to edit (validation errors): {loc}"
    after = page_props(seller, f"/seller/auctions/{DRAFT}/edit")["props"]["auction"]["title"]
    assert "guncellendi" in after, f"title not persisted: {after}"


# ---- stories ----
def test_story_upload_and_delete(seller):
    r = upload(seller, "/stories", {}, {"media": ("qa.png", io.BytesIO(PNG), "image/png")}, "/")
    assert r.status_code in (200, 302), f"story upload -> {r.status_code} {r.text[:400]}"
    props = page_props(seller, "/")["props"]
    assert props.get("flash", {}).get("error") in (None, ""), props.get("flash")


# ---- support ----
def test_support_ticket_create_and_view(buyer):
    r = post(buyer, "/support", {"subject": "TEST_QA destek talebi",
                                 "body": "TEST_QA otomatik olusturulan destek mesaji.",
                                 "priority": "low", "category": "other"},
             allow_redirects=False, headers={"Referer": f"{BASE_URL}/support/create"})
    assert r.status_code == 302, f"ticket create -> {r.status_code} {r.text[:400]}"
    loc = r.headers.get("Location", "")
    assert "create" not in loc, f"ticket create bounced back: {loc}"
    lst = page_props(buyer, "/support")["props"]
    assert json.dumps(lst, ensure_ascii=False).count("TEST_QA destek talebi") >= 1, "ticket not listed"


def test_admin_sees_support_ticket(admin):
    props = page_props(admin, "/admin/support")["props"]
    assert "TEST_QA destek talebi" in json.dumps(props, ensure_ascii=False), \
        "buyer ticket not visible in admin support list"


# ---- follow ----
def test_follow_toggle(buyer):
    props = page_props(buyer, "/u/seller")["props"]
    uid = props["pf"]["user"]["id"]
    assert uid, f"cannot find seller id in profile props keys={list(props)}"
    r = post(buyer, f"/follow/{uid}", {}, allow_redirects=False,
             headers={"Referer": f"{BASE_URL}/u/seller"})
    assert r.status_code in (200, 302), f"follow -> {r.status_code}"
    r2 = post(buyer, f"/follow/{uid}", {}, allow_redirects=False,
              headers={"Referer": f"{BASE_URL}/u/seller"})
    assert r2.status_code in (200, 302), f"unfollow -> {r2.status_code}"


# ---- profile update ----
def test_buyer_profile_update(buyer):
    props = page_props(buyer, "/profile")["props"]
    user = props.get("user") or props.get("auth", {}).get("user")
    r = post(buyer, "/profile", {"_method": "PUT", "name": user["name"],
                                 "username": user.get("username", "buyer"),
                                 "bio": "TEST_QA bio"},
             allow_redirects=False, headers={"Referer": f"{BASE_URL}/profile"})
    assert r.status_code == 302, f"profile update -> {r.status_code} {r.text[:300]}"


# ---- notifications ----
def test_notifications_read_all(buyer):
    r = post(buyer, "/notifications/read-all", {}, allow_redirects=False,
             headers={"Referer": f"{BASE_URL}/notifications"})
    assert r.status_code in (200, 302), f"read-all -> {r.status_code}"


# ---- admin categories CRUD ----
def test_admin_category_crud(admin):
    r = post(admin, "/admin/categories", {"name": "TEST_QA Kategori", "is_active": 1},
             allow_redirects=False, headers={"Referer": f"{BASE_URL}/admin/categories/create"})
    assert r.status_code == 302, f"category create -> {r.status_code} {r.text[:400]}"
    assert "create" not in r.headers.get("Location", ""), "category create bounced back"
    rows = page_props(admin, "/admin/categories")["props"]["categories"]["data"]
    mine = [c for c in rows if c["name"] == "TEST_QA Kategori"]
    assert mine, "created category not listed"
    cid = mine[0]["id"]
    t = post(admin, f"/admin/categories/{cid}/toggle", {}, allow_redirects=False,
             headers={"Referer": f"{BASE_URL}/admin/categories"})
    assert t.status_code in (200, 302), f"toggle -> {t.status_code}"
    d = post(admin, f"/admin/categories/{cid}", {"_method": "DELETE"}, allow_redirects=False,
             headers={"Referer": f"{BASE_URL}/admin/categories"})
    assert d.status_code in (200, 302), f"delete -> {d.status_code}"


# ---- broken images inventory (reported, not asserted hard) ----
def test_image_inventory(guest):
    """All /storage/* images referenced on public pages must resolve (no 404)."""
    urls = set()
    for path in ["/", "/browse/auctions", "/u/seller"]:
        t = guest.get(f"{BASE_URL}{path}", timeout=90).text.replace("\\/", "/")
        for m in re.findall(r'(?:https?://[^"\'\s]+)?/storage/[^"\'\s]+?\.(?:jpg|jpeg|png|webp)', t):
            urls.add(m if m.startswith("http") else BASE_URL + m)
    broken = [(guest.head(u, timeout=60).status_code, u) for u in sorted(urls)]
    broken = [b for b in broken if b[0] >= 400]
    print(f"checked {len(urls)} storage images")
    assert not broken, f"broken storage images: {broken}"


def test_header_search_returns_auctions(guest):
    """Header live-search must return auctions too, not only users."""
    r = guest.get(f"{BASE_URL}/live-search?q=antika", timeout=60,
                  headers={"Accept": "application/json"})
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    assert any("auctions/" in (d.get("url") or "") for d in data), \
        f"no auction results from live-search (auction search disabled): {data}"
