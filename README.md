# Oh See Gists

[![Current Release](https://img.shields.io/github/release/ohseesoftware/oh-see-gists.svg?style=flat-square)](https://github.com/ohseesoftware/oh-see-gists/releases)
![Build Status Badge](https://github.com/ohseesoftware/oh-see-gists/workflows/Build/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/ohseesoftware/oh-see-gists/badge.svg?branch=master)](https://coveralls.io/github/ohseesoftware/oh-see-gists?branch=master)
[![Maintainability Score](https://img.shields.io/codeclimate/maintainability/ohseesoftware/oh-see-gists.svg?style=flat-square)](https://codeclimate.com/github/ohseesoftware/oh-see-gists)
[![Downloads](https://img.shields.io/packagist/dt/ohseesoftware/oh-see-gists.svg?style=flat-square)](https://packagist.org/packages/ohseesoftware/oh-see-gists)
[![MIT License](https://img.shields.io/github/license/ohseesoftware/oh-see-gists.svg?style=flat-square)](https://github.com/ohseesoftware/oh-see-gists/blob/master/LICENSE)

Use GitHub Gists to embed your code snippets on your site. Example: https://ohseemedia.com/posts/hooks-can-only-be-called-inside-the-body-of-a-function-component-reactjs-error/

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

### Naming

There are two **very important** naming conventions you have to follow:

- The `gist_content` name for the fieldset is **very** important. The add-on references this key so you cannot change it.
- Your bard block has to be named `content` for the add-on to save to your GitHub Gists. Otherwise, you will be just creating code blocks on your Statamic site.

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
