/**
 * Admin > Destek > Talep Gösterim sayfası
 * — Yanıt gönderme (AJAX) + mesaj sayacı güncelleme
 *
 * Sayfa yapılandırması: #adminSupportShowRoot data-* nitelikleri
 *   data-reply-url  : POST endpoint
 *   data-msg-count  : Mevcut mesaj sayısı
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.getElementById('adminSupportShowRoot');
        if (!root || typeof jQuery === 'undefined') return;

        var replyUrl = root.dataset.replyUrl;
        var msgCount = parseInt(root.dataset.msgCount, 10) || 0;

        function escapeHtml(str) {
            return $('<div>').text(str == null ? '' : String(str)).html();
        }

        function buildBubble(body, name, time, avatar) {
            var imgSrc = avatar
                ? avatar
                : 'https://ui-avatars.com/api/?name=' +
                  encodeURIComponent(name) +
                  '&size=34&background=155eef&color=fff';

            return (
                '<div class="msg-bubble admin">' +
                    '<img class="msg-avatar" src="' + imgSrc + '" alt="' + escapeHtml(name) + '">' +
                    '<div class="msg-body">' +
                        '<div class="msg-text">' + escapeHtml(body) + '</div>' +
                        '<div class="msg-meta">🛡 Destek Ekibi (' + escapeHtml(name) + ') · ' + time + '</div>' +
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
                        buildBubble(res.message.body, res.message.user, res.message.time, res.message.avatar)
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
                        .html('<i class="bi bi-send"></i> Yanıt Gönder');
                }
            });
        });
    });
})();
