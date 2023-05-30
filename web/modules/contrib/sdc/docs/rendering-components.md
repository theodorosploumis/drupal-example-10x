_SDC_ lets you declare Drupal components that you can import and render in your
Drupal site.

#### The _Single Directory Components_

**SDC** implements the _single directory component_ approach. **Everything** you
need for your component is in a single directory.

This includes: `my-component.twig`, `my-component.css`, `my-component.js`, etc.
If it affects how your component renders, it's in that directory

This way your components are easier to find, don't have unaccounted code on some
other place, and **can be easily copy&pasted**.

What do you mean by "component"?
--------------------------------

In this context, a component is the combination of:

* A regular Twig template.
* Metadata describing the input data the template accepts (the `*.component.yml`
  file).
* Optional JavaScript.
* Optional Styles.

Check out the example components in the test folders for more details. A
component then **needs to be embedded in your _Drupal templates_**, otherwise it
just sits there waiting to be used. (Drupal templates are the templates that get
used by naming them with the correct convention,
like `node--article--card.twig.html`)

The mission of SDC is to **make Drupal theming simpler** by introducing _Single
Directory Components_. Front-end developers have enough on their plate, they
shouldn't need to know all the internals of Drupal.

#### Features of components

_1. Easy to embed_

Embed your component like you always embedded your template.

_You can use the familiar `include/embed` with the component's machine name_

_2. Libraries included_

JS and CSS files inside the component folder will be **included** during render.

_For caching reasons you can include `sdc/all` in your theme. This library
includes the CSS and JS for all the components._

_3. Props & slots_

Components can take structured data input via props, or any markup via slots.

_Props and slots are integrated in Drupal templates using the
standard [include](https://twig.symfony.com/doc/3.x/functions/include.html)
, [extend](https://twig.symfony.com/doc/3.x/tags/extends.html),
and [embed](https://twig.symfony.com/doc/3.x/tags/embed.html) from Twig._

How to create a component
-------------------------

A component is any directory, under `/components`, in your Drupal themes or
modules that contains a `my-component.component.yml`. This folder must also
contain at least a `my-component.twig` template. You can also
add `my-component.css` and `my-component.js` your stylesheets and JS scripts.
Learn more about creating a component in
the [documentation](writing-components.md).

How to render a component
-------------------------

In Drupal there are two main ways to render some HTML, via a render array, and
via Twig.

### Via render array

There is a special render element that allows you to render a component based
solely on the component ID and the component props. See this example on
rendering the my-button component with the label Click Me using
the `hook_page_top`:

```php
<?php
$page_top['cta-button'] = [
  '#type' => 'sdc',
  '#component' => 'my-button',
  '#context' => [
    'text' => t('Click Me'),
  ],
];
?>
```

This will take care of CSS, JS, HTML, and assets for you.

### Via Twig templates

Go to the template where you want to place your component, and `embed/include`
it.


#### With the traditional syntax: `include` or `embed`

Just provide the component ID of the template of the component. SDC will
add the CSS & JS if needed.

**Example of how to embed a button:**

    {{ include('sdc_examples:my-button', {
      text: 'Click Me',
      iconType: 'external'
    }) }}

**Example of how to render a slot in a card component:**

    {% embed 'sdc_examples:my-card' with {
      header: label
    } %}
      {% block card_body %}
        {{ content.field_media_image }}

          {{ content|without('field_media_image') }}

        {{ include('sdc_examples:my-button', { text: 'Like', iconType: 'like' }) }}
      {% endblock %}
    {% endembed %}
