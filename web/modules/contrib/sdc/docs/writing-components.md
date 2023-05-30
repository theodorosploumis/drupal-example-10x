# Writing Single Directory Components

## Assumptions

- Component templates use the file extension `.twig` (instead of `.html.twig`) -
  this prevents Drupal from picking up templates and rendering them when they
  share a name with a template Drupal already knows about.
- Components are structured like:

```console
<your theme/module>
    |- components
        |- my-component-machine-name
            |- README.md (documentation for component)
            |- thumbnail.png (thumbnail for component selectors)
            |- my-component-machine-name.twig (required)
            |- my-component.component.yml (required)
            |- my-component.js
            |- my-component.css
            |- assets
                |- img1.png
```

## Component Metadata

The `*.component.yml` metadata file is the component definition file. This file
defines the component and its API, and it is used to discover the component.

This project
includes [a JSON Schema](../src/metadata-full.schema.json) for the
`*.component.yml` developers will write. This is located in
`sdc/metadata.schema.json`.

### Extending the Component Metadata

Developers can extend the component metadata to support new features for
components. Imagine a module that creates forms for a component props and slots
so editors can create blocks out of some components (`sdc_block`). One could add
block support on a particular component by adding `sdc_block` metadata to it.

```yaml
$schema: https://git.drupalcode.org/project/sdc/-/raw/1.x/src/metadata.schema.json
machineName: my-image
name: Image
status: BETA
thirdPartyProperties:
  sdc_block:
    block: true
    wysiwyg: true
schemas:
  props:
    type: object
    required:
      - caption
    properties:
      caption:
        type: string
        title: Caption
        description: The caption for the image
        examples:
          - A bird eating grass seeds.
```

Mind the `thirdPartyProperties` above. You will also note that all settings
must be prefixed by the provider module.

## Twig templates

The folder that contains the `my-component.component.yml` is the _component
directory_. For a component to be valid there needs to be at least
one `my-component.twig` file. That is considered to be the main template for the
component. Component templates have several variables available to them:

- `sdcAttributes`: an object with HTML attributes meant for the root of your
  component.
  Check [this example](https://git.drupalcode.org/project/sdc/-/blob/1.x/examples/components/my-button/my-button--primary.twig)
  component to see it in action.
- `sdcMeta`: an object containing the component metadata:
  - `path`: the path to the component
  - `uri`: the URI pointing to the component folder. Useful to link to static
    assets.
  - `machineName`: the component machine name.
  - `status`: the component status.
  - `name`: the human-readable name for the component.
  - `group`: the component group.

## Stylesheets and Scripts

The component discovery looks for `my-component.js` and `my-component.css` in
the root of the component directory. Then the module creates a dynamic library
with the name `sdc/<my-component-id>`. This library is included with the
component template whenever the component is rendered using one of the methods
described in the project page.

### Compiled assets

If you want to use modern JavaScript features, or need to compile SCSS (or use
PostCSS) you can use your preferred tools. You need to make sure that your
pipeline outputs the processed files
in `<module-or-theme>/components/my-component`
as expected.

There are two typical setups:

- Develop your components directly in their final location, and use a task
  runner to generate `my-component.css` and `my-component.js` based on
  `main.scss` and `main.ts` (for instance).
- Develop your components in `<module-or-theme>/components/src/my-component`
  and output the processed files in `<module-or-theme>/components/my-component`.
  Remember that your pipeline should also copy the rest of the necessary files,
  otherwise the resulting component folder would be invalid.
