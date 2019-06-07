# prism-woo
Base theme for woocommerce development

## Wordpress docker-compose

[wordpress-docker-compose](https://github.com/nezhar/wordpress-docker-compose) 

Clone repo and follow directions to setup local environment.


### Starting/stopping container
```docker-compose start```

```docker-compose stop```

### Developing a Theme

Clone this repo into the root directory of the wordpress-docker-compose project and rename the theme.

```git clone https://github.com/ekayy/prism-woo theme_name```

Then configure the volume to load the theme in the container in the docker-compose.yml by uncommenting and then renaming the _theme_name_:

```
volumes:
  - ./theme-name/trunk/:/var/www/html/wp-content/themes/theme-name
```

### Using WP CLI

The docker compose configuration also provides a service for using the [WordPress CLI](https://developer.wordpress.org/cli/commands/).

Sample command to install WordPress:

```
docker-compose run --rm wpcli core install --url=http://localhost --title=test --admin_user=admin --admin_email=admin@example.com --admin_password=password
```

For an easier usage you may consider adding an alias for the CLI:

```alias wp="docker-compose run --rm wpcli"```

This way you can use the CLI command above as follows:

```wp plugin list```



## Theme development

### Setup theme

Install woocommerce and storefront theme:

```wp plugin install  --activate woocommerce woocommerce-admin```

```wp theme install  --activate storefront```

```wp theme activate theme_name```


Go into the theme folder (default is _./theme-name/trunk_):

Install dependencies:

```npm i```

### Gulp

Watch scripts & sass directories and autoreload browser using browsersync:

```gulp```

