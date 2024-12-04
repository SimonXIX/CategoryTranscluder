# ExoticTransclusions MediaWiki extension

The ExoticTranclusions extension is an extension for MediaWiki that performs several exotic transclusion functions: 

- a parser hook transforms every category page so that the page transcludes content from every page within that category
- a custom tag allows transclusion of text from external websites

## installation

* [Download](https://github.com/SimonXIX/ExoticTransclusions/archive/refs/heads/main.zip) and move the extracted `ExoticTransclusions` folder to your `extensions/` directory. Developers and code contributors should install the extension from Git instead, using:

```
cd extensions/
git clone https://github.com/SimonXIX/ExoticTransclusions
```

* Add the following code at the bottom of your [LocalSettings.php](https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:LocalSettings.php) file:

```
wfLoadExtension( 'ExoticTransclusions' );
```

* **Done** – Navigate to [Special:Version](https://www.mediawiki.org/wiki/Special:Version) on your wiki to verify that the extension is successfully installed.

## CategoryTranscluder

The CategoryTranscluder function is automatically applied to all category pages. Every category page is transformed such that the page transcludes content from every page within that category.

## TranscludeWeb

To transclude text from an external webpage, use the custom tag:

```
<transclude url="[URL]"></transclude>
```

or with the optional `section` parameter:

```
<transclude url="[URL]" section="//[SECTION]"></transclude>
```

For example:

```
<transclude url="https://constantvzw.org/wefts/unboundlibraries-eva.en.html" section="//section[@id='sameness-and-difference'],//section[@id='structural-hierarchies']"></transclude>
```

### parameters

`url`: The `url` parameter should be the URL of the webpage you wish to transclude

`section`: The `section` parameter allows you to specify a section of the page that you wish to transclude using a CSS selector or XPath expression. This either takes a single CSS selector or XPath expression or a comma-separated list of CSS selectors or XPath expressions for multiple sections. 

