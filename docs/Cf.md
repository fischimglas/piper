# Config reader and provider

Reads configuration values from a YAML file located at etc/config.yaml.

## Usage:

```php
$value = Cf::get('GoogleAiAdapter.apiKey')
```

### Autoload config for classes

Use: For new Adapters or other custom extensions, as well as all existing adapters and filters.

```php
For dedicated classes such as Adapters, you can autoload the configuration by calling in the constructor:

```php
Cf::autoload($this)
```
