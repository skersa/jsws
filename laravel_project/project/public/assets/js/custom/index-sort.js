/**
 * Ana sayfa (index) — sıralama değerini window.idxCurrentSort'a yükler.
 * Kaynak: <body data-idx-sort="..."> veya #idxSortRoot data-sort="..."
 */
(function () {
    'use strict';

    var root = document.getElementById('idxSortRoot');
    var sort = (root && root.dataset.sort) || document.body.dataset.idxSort;
    if (sort) window.idxCurrentSort = sort;
})();
