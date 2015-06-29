Add in your src/app/routing.yml

```yml
flamingo:
    resource: "@EarlsFlamingoCommandQueueBundle/Resources/config/routing.yml"
    prefix:   /flg
```

 After that you can access a simple interface at "/flg/script_list".

Template Layout
=======
If you want to add your own layout, you can add in services.yml

```yml
parameters:
  flamingo.admin.template:                      "EarlsFlamingoCommandQueueBundle::layout.html.twig"
```