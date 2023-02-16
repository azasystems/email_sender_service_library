## Как настроить nginx возвращать 403 для всех страниц, кроме script.php

Вы можете использовать следующую конфигурацию в файле nginx.conf:

```
location / { 
    deny all; 
} 

location = /script.php { 
    allow all; 
}
```
