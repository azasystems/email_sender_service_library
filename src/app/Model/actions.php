<?php

declare(strict_types=1);

namespace AzaSystems\App\Model;

//0,Почту пользователь не подтвердил по ссылке
const EMAIL_NOT_CONFIRMED = 0;

//1,Почта не проверена и валидность не ясна
const EMAIL_NOT_VALIDATED = 1;

//2,Почта проверена и невалидна
const EMAIL_NOT_VALID = 2;

//3,Почта проверена и валидна
const EMAIL_IS_VALID = 3;

//4,"Почта проверена и валидна, но уже истек срок валидности почты, нужна повторная валидация"
const EMAIL_IS_EXPIRED = 4;
