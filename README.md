# libi18n

Simple internationalized virion library

(For PocketMine-MP)

## How to use

### Create the locale file

All translated messages are in the `path/to/locale/<language>.yml` file.

Locale folders can be placed anywhere.

The explanation here assumes that you have the following locale files.

resouces/locale/eng.yml

```yaml
_:
  name: English
messages:
  errors:
    login: Failed to login
  hello: Hello %{player}!
items:
  diamond: Diamond
pjz9n:
  libi18n_example:
    Main:
      helloworld: Hello world!
    forms:
      ShopForm:
        buy: You bought %{item} for %{price}.
```

resources/locale/jpn.yml

```yaml
_:
  name: 日本語
messages:
  errors:
    login: ログインに失敗しました
  hello: こんにちは%{player}！
items:
  diamond: ダイヤモンド
pjz9n:
  libi18n_example:
    Main:
      helloworld: こんにちは世界！
    forms:
      ShopForm:
        buy: あなたは%{price}で%{item}を購入しました。
```

### Prepare LangInstance

#### New LangInstance object
This must be created for each language.

```php
new pjz9n\libi18n\LangInstance("jpn", "eng", "path/to/locale/");
```

Check the comments on the source code for the details of the constructor.

#### Use LangHolder
You can use the `pjz9n\libi18n\LangHolder` class to hold a `pjz9n\libi18n\LangInstance` instance.

in `pocketmine\plugin\Plugin` class

```php
public function onEnable() {
    echo $config->get("language");//jpn
    pjz9n\libi18n\LangHolder::init(new pjz9n\libi18n\LangInstance($config->get("language"), "eng", "path/to/locale/"));
}

public function foo () {
    echo pjz9n\libi18n\LangHolder::t(".helloworld");//こんにちは世界！
}
```

### Easy

To access strings in a nested array, specify a dot-separated string.

```php
/** @var pjz9n\libi18n\LangInstance $lang */
$lang->translate("messages.errors.login");
```

#### Result

eng: Failed to login

jpn: ログインに失敗しました

### Parameters

%{**Here**} This is the key to the associative array.

```php
/** @var pjz9n\libi18n\LangInstance $lang */
$lang->translate("messages.hello", ["player" => "PJZ9n"]);
```

#### Result

eng: Hello PJZ9n!

jpn: こんにちはPJZ9n！

### Relative key

You can infer the translation key from the class name.

In this example, it resolves as `pjz9n.libi18n_example.Main.helloworld`.

in `pjz9n\libi18n_example\Main` class

```php
/** @var pjz9n\libi18n\LangInstance $lang */
$lang->translate(".helloworld");
```

#### Result

eng: Hello world!

jpn: こんにちは世界！

### Prefix

If the prefix (%) is found, it is interpreted as the translation key from that position.

```php
/** @var pjz9n\libi18n\LangInstance $lang */
$lang->translate("[ERROR] %messages.errors.login");
```

### Result

eng: [ERROR] Failed to login

jpn: [ERROR] ログインに失敗しました

### Suffix

By putting a suffix (%), you can specify that it is the end of the translation key.

```php
/** @var pjz9n\libi18n\LangInstance $lang */
$lang->translate("[ERROR] %messages.errors.login%!!!");
```

### Result

eng: [ERROR] Failed to login!!!

jpn: [ERROR] ログインに失敗しました!!!

### TextFormat

The text format is independent for each parameter.

in `pjz9n\libi18n_example\forms\ShopForm` class

```php
/** @var pjz9n\libi18n\LangInstance $lang */
$lang->translate("§l§a%.buy", ["item" => "§eApple", "price" => "§c$1000"]);
```

#### Result

eng: §l§aYou bought §r§eApple§r§a§l for §r§c$1000§r§a§l.
![§l§aYou bought §r§eApple§r§a§l for §r§c$1000§r§a§l.](https://user-images.githubusercontent.com/38120936/122003846-945d8700-cdee-11eb-8735-5b06f8412c02.png)

jpn: §l§aあなたは§r§c$1000§r§a§lで§r§eApple§r§a§lを購入しました。
![§l§aあなたは§r§c$1000§r§a§lで§r§eApple§r§a§lを購入しました。](https://user-images.githubusercontent.com/38120936/122003842-93c4f080-cdee-11eb-8e6a-965370a1569d.png)

### Parameter translate

You can translate it by specifying the `pjz9n/libi18n/Translation` object as a parameter.

You can specify the parameter in the constructor second argument `parameter` of the `pjz9n/libi18n/Translation` object.

It can be nested indefinitely.

in `pjz9n\libi18n_example\forms\ShopForm` class

```php
/** @var pjz9n\libi18n\LangInstance $lang */
$lang->translate("§l§a%.buy", ["item" => new pjz9n\libi18n\Translation("§e%items.diamond"), "price" => "§c$1000"]);
```

#### Result

eng: §l§aYou bought §r§eDiamond§r§a§l for §r§c$1000§r§a§l.
![§l§aYou bought §r§eDiamond§r§a§l for §r§c$1000§r§a§l.](https://user-images.githubusercontent.com/38120936/122004273-29608000-cdef-11eb-8077-30ebfca4911b.png)

jpn: §l§aあなたは§r§c$1000§r§a§lで§r§eダイヤモンド§r§a§lを購入しました。
![§l§aあなたは§r§c$1000§r§a§lで§r§eダイヤモンド§r§a§lを購入しました。](https://user-images.githubusercontent.com/38120936/122004287-2cf40700-cdef-11eb-8bb6-27ceb784b538.png)
