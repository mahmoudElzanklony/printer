<?php
return [
    'pi pi-fw pi-home'=>[
        'label'=>'الرئيسية',
        'url'=>'/'
    ],
    'pi pi-users'=>[
        'label'=>'المستخدمين',
        'url'=>'/users'
    ],
    'pi pi-sitemap'=>[
        'label'=>'الاقسام',
        'url'=>'/categories'
    ],
    'pi pi-palette'=>[
        'label'=>'الخصائص',
        'url'=>'/properties-headings'
    ],
    'pi pi-wrench'=>[
        'label'=>'الصفات',
        'url'=>'/properties'
    ],
    'pi pi-cart-plus'=>[
        'label'=>'الطلبات',
        'url'=>'/orders'
    ],
    'pi pi-bookmark-fill'=>[
        'label'=>'الكوبونات',
        'url'=>'/coupons'
    ],
    'pi pi-cogpi-cog'=>[
        'label'=>'الخدمات',
        'url'=>'/services'
    ],
    'pi pi-money-bill'=>[
        'label'=>'الضريبة',
        'url'=>'/tax-value'
    ],
    'pi pi-megaphone'=>[
        'label'=>'الاعلانات',
        'url'=>'/ads'
    ],
    'fa-solid fa-comment-sms'=>[
        'label'=>'الرسائل الهاتفية',
        'url'=>'/dynamic-sms'
    ],
    'pi pi-send'=>[
        'label'=>'رسائل الدعم',
        'url'=>'/contacts'
    ],
    'fa-solid fa-earth-americas'=>[
        'label'=>'الدول',
        'url'=> '/shipment/countries',
        'parent'=>[
            'icon'=>'fa-solid fa-truck-fast',
            'label'=>'الشحن'
        ]
    ],
    'fa-solid fa-city'=>[
        'label'=>'المدن',
        'url'=> '/shipment/cities',
        'parent'=>[
            'icon'=>'fa-solid fa-truck-fast',
            'label'=>'الشحن'
        ]
    ],
    'fa-solid fa-hand-holding-dollar'=>[
        'label'=>'المناطق',
        'url'=> '/shipment',
        'parent'=>[
            'icon'=>'fa-solid fa-truck-fast',
            'label'=>'الشحن'
        ]
    ],
    'fa-solid fa-truck-fast'=>[
        'label'=>'الشحن',
        'check_first'=>true,
        'items'=>[
            [
                'label'=>'الدول',
                'icon'=> 'fa-solid fa-earth-americas',
                'url'=> '/shipment/countries',
            ],
            [
                'label'=>'المدن',
                'icon'=> 'fa-solid fa-city',
                'url'=> '/shipment/cities',
            ],
            [
                'label'=>'سعر التوصيل',
                'icon'=> 'fa-solid fa-hand-holding-dollar',
                'url'=> '/shipment',
            ]
        ]
    ],


];
