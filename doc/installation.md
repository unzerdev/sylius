# Installation

- Copy next line under 'imports' in Sylius config/packages/_sylius.yaml directory:
    ```bash 
  - { resource: "@SyliusUnzerPlugin/src/Resources/config/config.yaml" }
    ```

- Require Unzer repositories in composer.json (This is required step until repository is not public):
```bash
  "repositories": [
      {
         "type": "vcs",
         "url": "git@github.com:logeecom/UnzerSylius"
      },
      {
         "type": "vcs",
         "url": "git@github.com:logeecom/unzer-core.git"
      }
  ]
   ```
- Run: 
      composer require unzer/sylius-plugin

- Run migrations in Sylius root directory:
    ```bash
  bin/console doctrine:migrations:migrate
    ```

- Import the routing in your config/routes.yaml file in Sylius root directory:
    ```bash
  unzer_routes:
    resource: "@SyliusUnzerPlugin/src/Resources/config/routing.yaml"
    
  unzer_admin_routes:
    resource: "@SyliusUnzerPlugin/src/Resources/config/admin_routing.yaml"
    prefix: '/%sylius_admin.path_name%'
    ```

- Installing assets without webpack: Run following command in Sylius root directory:
    ```bash 
    bin/console assets:install
    ```
## Unzer Encryption Key

The `UNZER_ENCRYPTION_KEY` exists with a default value. It is **not recommended** to use this in production systems. Instead, you should run the following command:

```bash
php bin/console sylius:unzer-key:create
```
After generating the key, set the value as an environment variable (UNZER_ENCRYPTION_KEY) for your environment.

## Checkout setup
- Override how Unzer payment method is rendered on the checkout buy wrapping original content of `templates/bundles/SyliusShopBundle/Checkout/SelectPayment/_choice.html.twig` file with following condition
    ```html 
    {% if method.gatewayConfig.factoryName == 'unzer_payment' %}                                                                
        {% include '@SyliusUnzerPlugin/Checkout/SelectPayment/_choiceUnzer.html.twig'%}
    {% else %}
        <!--    Original file content goes here    -->
    {% endif %}
    ```
- Override how Unzer payment method is rendered on the checkout complete page `templates/bundles/SyliusShopBundle/Common/Order/_payments.html.twig`
    ```shell 
     cp vendor/unzer/sylius-plugin/tests/Application/templates/bundles/SyliusShopBundle/Common/Order/_payments.html.twig templates/bundles/SyliusShopBundle/Common/Order/_payments.html.twig
    ```  

## Cache clear

- Run the following command to clear the store cache to ensure translations function correctly:

```bash
php bin/console cache:clear
```
