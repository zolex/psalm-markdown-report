# Psalm Markdown Report

Github Markdown report plugin for [Psalm](https://github.com/vimeo/psalm) >= 6.0

## Installation

```
composer require --dev zolex/psalm-markdown-report
vendor/bin/psalm-plugin enable Zolex\PsalmMarkdownReport\Plugin
```

## Configuration

If you want to use the markdown report in github pull request comment, you should provide the branch in en environment variable of your action so the file links in the report point to the branch of the pull request

```bash
MARKDOWN_LINK_BRANCH=feature/something
```

A github action to add the report as a PR comment could look as follows:

```yaml
    steps:
      - name: Run Psalm
        env:
          MARKDOWN_LINK_BRANCH: ${{ github.head_ref || github.ref_name }}
        run: tools/psalm/vendor/bin/psalm

      - name: Add report to PR
        uses: mshick/add-pr-comment@v2
        with:
          message-path: ./psalm-report.md
          message-id: psalm
```

By default, the plugin writes the file `psalm-report.md` in the working directory. You can change it in `psalm.xml` or `psalm.xml.dist` by adding a `reportPath` element in the plugin config

```xml

<plugins>
    <pluginClass class="Zolex\PsalmMarkdownReport\Plugin">
        <reportPath>./folder/report.markdown</reportPath>
    </pluginClass>
</plugins>
```