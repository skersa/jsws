/**
 * Kullanıcı > Destek > Talep Gösterim sayfası
 * — Yanıt gönderme (AJAX) + FAQ akordeon
 *
 * Sayfa yapılandırması: #supportShowRoot data-* nitelikleri
 *   data-reply-url  : POST endpoint
 *   data-msg-count  : Mevcut mesaj sayısı
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.getElementById('supportShowRoot');
        if (!root || typeof jQuery === 'undefined') return;

        var replyUrl = root.dataset.replyUrl;
        var msgCount = parseInt(root.dataset.msgCount, 10) || 0;

        function escapeHtml(str) {
            return $('<div>').text(str == null ? '' : String(str)).html();
        }

        function buildBubble(body, name, time, isAdmin, avatar) {
            var bg = isAdmin ? '155eef' : '10b981';
            var side = isAdmin ? 'admin' : '';
            var sender = isAdmin ? 'Destek Ekibi' : escapeHtml(name);
            var imgSrc = avatar
                ? avatar
                : 'https://ui-avatars.com/api/?name=' +
                  encodeURIComponent(name) +
                  '&size=34&background=' + bg + '&color=fff';

            return (
                '<div class="msg-bubble ' + side + '">' +
                    '<img class="msg-avatar" src="' + imgSrc + '" alt="' + escapeHtml(name) + '">' +
                    '<div class="msg-body">' +
                        '<div class="msg-text">' + escapeHtml(body) + '</div>' +
                        '<div class="msg-meta">' + sender + ' · ' + time + '</div>' +
                    '</div>' +
                '</div>'
            );
        }

        $('#reply-form').on('submit', function (e) {
            e.preventDefault();

            var body = $('#reply-body').val().trim();
            if (!body) return;

            $('#reply-error').addClass('d-none').text('');
            $('#reply-btn')
                .prop('disabled', true)
                .html('<i class="bi bi-hourglass-split"></i> Gönderiliyor...');

            $.ajax({
                url: replyUrl,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    body: body
                },
                success: function (res) {
                    $('#reply-body').val('');
                    $('#msg-list').append(
                        buildBubble(
                            res.message.body,
                            res.message.user,
                            res.message.time,
                            res.message.is_admin,
                            res.message.avatar
                        )
                    );
                    msgCount++;
                    $('#msg-count').text(msgCount + ' mesaj');
                    var list = $('#msg-list');
                    list.scrollTop(list.prop('scrollHeight'));
                },
                error: function (xhr) {
                    var err =
                        (xhr.responseJSON &&
                            xhr.responseJSON.errors &&
                            xhr.responseJSON.errors.body &&
                            xhr.responseJSON.errors.body[0]) ||
                        'Bir hata oluştu, tekrar deneyin.';
                    $('#reply-error').removeClass('d-none').text(err);
                },
                complete: function () {
                    $('#reply-btn')
                        .prop('disabled', false)
                        .html('<i class="bi bi-send"></i> Gönder');
                }
            });
        });

        /* ─── FAQ akordeon ─── */
        document.querySelectorAll('[data-faq]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = btn.closest('.support-faq-item');
                if (!item) return;
                var isOpen = item.classList.contains('open');
                document.querySelectorAll('.support-faq-item.open').forEach(function (el) {
                    el.classList.remove('open');
                });
                if (!isOpen) item.classList.add('open');
            });
        });
    });
})();
