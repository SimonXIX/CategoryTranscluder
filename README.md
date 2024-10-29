# CategoryTranscluder MediaWiki extension

The CategoryTranscluder extension is a parser hook extension for MediaWiki that transforms every category page so that the page transcludes content from every page within that category. 

## Installation

* [Download](https://github.com/SimonXIX/CategoryTranscluder/archive/refs/heads/main.zip) and move the extracted `CategoryTranscluder` folder to your `extensions/` directory. Developers and code contributors should install the extension from Git instead, using:

```
cd extensions/
git clone https://github.com/SimonXIX/CategoryTranscluder
```

* Add the following code at the bottom of your [LocalSettings.php](https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:LocalSettings.php) file:

```
wfLoadExtension( 'CategoryTranscluder' );
```

* **Done** – Navigate to [Special:Version](https://www.mediawiki.org/wiki/Special:Version) on your wiki to verify that the extension is successfully installed.
