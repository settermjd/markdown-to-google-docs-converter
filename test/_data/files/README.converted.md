# Markdown to Google Docs Converter

This is a small project, based around [Mini Mezzio][mini-mezzio], that converts Markdown files to OpenOffice Documents.

> [!NOTE]
> The current scope of the project is an interim step towards the ultimate goal of creating a Google Doc from a Markdown file.
> At this stage, I don't want to complicate the process unnecessarily, so am just focusing on the Markdown to ODT stage.

## Requirements

To use the application, you'll need the following:

- [Composer][composer] installed globally
- [Git][git]
- [PHP 8.4][php]
- [Pandoc][pandoc]

## Application Overview

The application is a small, web-based application that allows the user to upload a Markdown file, which will then be converted to an OpenOffice Document (ODT) file and sent back to the user as a download.

## Usage

### Starting the application

To start the application, run the following command:

~~~
```bash
composer serve
```
~~~

This will launch the application listening on port 8080 using PHP's built-in web server.

### Stopping the application

To stop the application, in the same terminal tab or session that you started it, press <kbd>ctrl</kbd> + <kbd>c</kbd>.

### Using the application

After starting the application, open http://localhost:8080 in your browser of choice.
It should look like the screenshot below.

![A screenshot of the application's default route, loaded in Firefox.](./docs/images/screenshots/default-route.png)

Choose a Markdown file from your local filesystem to upload, and submit the form.
A few moments later, you should receive an ODT version of the Markdown file as a download.

### How to generate a new ODT data file

Internally, the application escapes both inline and fenced code blocks using \`MarkdownConverterService\` uses [Pandoc][pandoc] to convert the uploaded Markdown file to OpenOffice Document format, using a custom style document stored in _/data_.
To generate another one, use the following command:

~~~
```bash
pandoc --output data/templates/custom-styles.odt \
    --print-default-data-file reference.odt
```
~~~

Then, open _data/templates/custom-styles.odt_ with [LibreOffice][libreoffice] and update the document's styles, not the document itself, to suit your needs.

<!-- File Links -->
[composer]: https://getcomposer.org/
[git]: https://git-scm.com/downloads
[libreoffice]: https://www.libreoffice.org/
[mini-mezzio]: https://github.com/asgrim/mini-mezzio
[pandoc]: https://pandoc.org/
[php]: https://www.php.net/