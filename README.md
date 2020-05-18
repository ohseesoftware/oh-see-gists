# Oh See Gists

## Installation

Install the add-on:

`composer require ohseesoftware/oh-see-gists`

Publish the add-on's assets:

`php artisan vendor:publish --tag=oh-see-gists`

This will publish:

- a config file for the GitHub API
- views in the `resources/views` directory
- fieldsets in the `resources/fieldsets` directory

## Usage

### Add your GitHub token to your .env file

You'll need to create a new personal access token. You can do so here: [https://github.com/settings/tokens/new](https://github.com/settings/tokens/new).

The token only needs the `gist` scope.

Add the token as `OH_SEE_GISTS_GITHUB_TOKEN` in your `.env` file.

### Add the fieldset to your blueprint(s)

The fieldset that will be published is named `gist_block`. You will need to update your blueprints to reference the fieldset wherever you want it to be used. As an example in bard:

```yaml
type: bard
sets:
  gist_content:
    display: Gist
    fields:
      - import: gist_block
```

The `gist_content` name for the fieldset is **very** important. The add-on references this key so you cannot change it.

### Use the partial in your templates

The add-on publishes a partial for you to use in your templates to render the Gists. You can use it like so:

<!-- prettier-ignore-start -->
```html
{{ bard_content }}
    {{ if type == "gist_content" }}
        {{ partial src="partials/gist_content" }}
    {{ /if }}
{{ /bard_content }}
```
<!-- prettier-ignore-end -->
