<?php

return [

    'accepted' => ':attribute kabul edilmelidir.',
    'accepted_if' => ':other :value olduğunda :attribute kabul edilmelidir.',
    'active_url' => ':attribute geçerli bir URL olmalıdır.',
    'after' => ':attribute :date tarihinden sonra olmalıdır.',
    'after_or_equal' => ':attribute :date tarihinden sonra veya eşit olmalıdır.',
    'alpha' => ':attribute sadece harf içermelidir.',
    'alpha_dash' => ':attribute sadece harf, sayı ve tire içermelidir.',
    'alpha_num' => ':attribute sadece harf ve sayı içermelidir.',
    'array' => ':attribute bir dizi olmalıdır.',
    'before' => ':attribute :date tarihinden önce olmalıdır.',
    'before_or_equal' => ':attribute :date tarihinden önce veya eşit olmalıdır.',

    'between' => [
        'numeric' => ':attribute :min ile :max arasında olmalıdır.',
        'file' => ':attribute :min ile :max KB arasında olmalıdır.',
        'string' => ':attribute :min ile :max karakter arasında olmalıdır.',
        'array' => ':attribute :min ile :max arasında eleman içermelidir.',
    ],

    'boolean' => ':attribute alanı doğru ya da yanlış olmalıdır.',
    'confirmed' => ':attribute onayı uyuşmuyor.',
    'current_password' => 'Mevcut şifre yanlış.',
    'date' => ':attribute geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute :date tarihine eşit olmalıdır.',
    'date_format' => ':attribute formatı :format olmalıdır.',
    'different' => ':attribute ve :other farklı olmalıdır.',
    'digits' => ':attribute :digits basamaklı olmalıdır.',
    'digits_between' => ':attribute :min ile :max basamak arasında olmalıdır.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'file' => ':attribute dosya olmalıdır.',
    'image' => ':attribute resim olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',
    'integer' => ':attribute tam sayı olmalıdır.',
    'max' => [
        'numeric' => ':attribute en fazla :max olabilir.',
        'string' => ':attribute en fazla :max karakter olabilir.',
        'file' => ':attribute en fazla :max KB olabilir.',
        'array' => ':attribute en fazla :max eleman içerebilir.',
    ],

    'min' => [
        'numeric' => ':attribute en az :min olmalıdır.',
        'string' => ':attribute en az :min karakter olmalıdır.',
        'file' => ':attribute en az :min KB olmalıdır.',
        'array' => ':attribute en az :min eleman içermelidir.',
    ],

    'not_in' => 'Seçilen :attribute geçersiz.',
    'numeric' => ':attribute sayı olmalıdır.',
    'required' => ':attribute alanı zorunludur.',
    'same' => ':attribute ve :other aynı olmalıdır.',
    'string' => ':attribute metin olmalıdır.',
    'unique' => ':attribute zaten kullanılıyor.',

    'url' => ':attribute geçerli bir URL olmalıdır.',


    'attributes' => [
        'name' => 'Ad Soyad',
        'email' => 'E-posta',
        'password' => 'Şifre',
        'phone' => 'Telefon',
        'role' => 'Hesap Türü',
        'password_confirmation' => 'Şifre Tekrar',
    ],
];