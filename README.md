# :new_moon_with_face: 常用**PHP**代码片段

# 开发规范

## **必须**

- `类与方法`命名规范:  **驼峰命名法**
- 使用 `PSR-4` 代码规范
- `PHP` >= **7.1.0**

# 目的

- [x] :shipit: 常用的PHP代码碎片
- [x] 方便 **个人** 引用查询使用
- [x] 代码备份
- [x] 项目开发

> 更多内容,可以访问[:rocket: ITAKEN GITHUB PAGES](https://itaken.github.io)

# 引入

## `composer.json`文件中引入

```json
{
    "require": {
        "itaken/php-pie": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/itaken/php-pie.git"
        }
    ],
    "minimum-stability": "dev"
}
```

然后执行

```
composer update itaken/php-pie
```

## 文件引入

```php
require "/path/to/php-pie/autoload.php"
```

# LICENSES

Copyright [itaken]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.