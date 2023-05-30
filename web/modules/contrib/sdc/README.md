_Single Directory Components_ lets you declare Drupal components that you can
import and render in your Drupal site.

What do you mean by "component"?
--------------------------------

In this context, a component is the combination of:

* A regular Twig template.
* Metadata describing the input data the template accepts (
  the `my-component.component.yml` file).
* Optional JavaScript.
* Optional Styles.
* Optional README.md with component documentation.
* Optional thumbnail.

A component then **needs to be embedded in your _Drupal templates_**, otherwise
it just sits there waiting to be used. (Drupal templates are the templates that
get used by naming them with the correct convention,
like `node--article--card.twig.html`) In this context, a component is **not**: a
type of a block plugin, or a Twig template by itself.

[![A component's structure](https://www.drupal.org/files/ksnip_20220313-095710.png)](https://www.drupal.org/files/ksnip_20220313-095710.png)

How to create a component
-------------------------

A component is any directory in your Drupal install that contains
a `my-component.component.yml`. This folder must also contain at least
a `my-component.twig` template. It is advised to create a `css` and `js`
directory for your stylesheets and JS scripts. Learn more about creating a
component in
the [documentation](./docs/writing-components.md)
. You will need to let Drupal know where to start scanning for components (
including subdirectories). You can add as many locations as you need in the
settings page (`admin/config/user-interface/sdc`).

How to render a component
-------------------------

In Drupal there are two main ways to render some HTML, via a render array, and
via Twig.

### Using a render array

There is a special render element that allows you to render a component based
solely on the component ID and the component props. See this example on
rendering the `my-button` component with the label _Click Me_ using
the `hook_page_top`:

```php
// hook_page_top
$page_top['cta-button'] = [
  '#type' => 'sdc',
  '#component' => 'my-button',
  '#context' => [
    'text' => t('Click Me'),
  ],
];
```

This will take care of CSS, JS, HTML, and assets for you.

### Via Twig templates

There are two syntaxes for rendering components, both of those will offer these
features:

### Easy to embed

Embed your component like you always embedded your template.

_You can use the familiar `include/embed` with the path to the component's
template_

### Libraries included

JS and CSS files inside the component folder will be **included** during render.

_For caching reasons you can include `sdc/all` in your theme. This
library includes the CSS and JS for all the components._

### Additional context

The templates for your components will receive **additional context** that will
make your theming experience more flexible and powerful.

_Learn more about the additional context
in [the documentation](https://git.drupalcode.org/project/sdc/-/blob/1.x/docs/writing-components.md#twig-templates)
._

#### With the traditional syntax: `include` or `embed`

Just provide the ID of the component. Single Directory Components will check if the template
is for a components, and add the CSS & JS if it is. Example:

```twig
{% include "my-button--primary" with {
  text: 'Click Me',
  iconType: 'external'
} %}
```

Composing "top-down" components
-------------------------------

**Traditionally**, when you are not using this module, in order to map your
templates, you need to start at the content type template (for instance) and
then at some point you render `{{ content }}`. This is where the render pipeline
enters the cognitive black hole, you lose the thread. To find the thread again
you need to leave the IDE to your browser to inspect the HTML in search of
template name suggestions for your fields/sub-components. After that you create
the new twig template, etc. **With this module you still start at the content
type template and you do everything there, if you want.** That is because you
will develop all your components separately and wire the data to in the Drupal
templates. Consider the following example for a custom
block:

<a href="https://www.drupal.org/files/ksnip_20220418-005414.png">
 <img alt="Component from Twig" src="https://www.drupal.org/files/ksnip_20220418-005414.png"/>
</a>

The `my-card` component could be provided by a contributed module, a custom
module for your component library, or in the same theme that
holds `block--bundle--block-cta.twig.html`. Note that the `my-card` component
supports children HTML and children components, _similar_ to **how React works**
. With this technique you can map all the field data in its place by using the
necessary sub-components. Thus, you don't have to find the sub-templates to map
them to their corresponding components.
