```php
<?php

->where(new RawSql("id = 1"))
->where(new RawSql("id = ?", 1))
->where(new RawSql("id = 1 AND name = 'Jan'"))
->where(new RawSql("id = ? AND name = ?", [1, 'Jan']))
->where(new RawSql("id IN (1,2,3) AND name = 'Jan'"))
->where(new RawSql("id IN (?) AND name = ?', [[1,2,3], 'Jan"))
->where(new RawSql("name = surname"))
->columns(
    [
        'name' => new RawSql("IFNULL(`name`, 'noname')"), // IFNULL('name', 'noname') AS `name`
    ]
])
