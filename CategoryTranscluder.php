<?php

use MediaWiki\MediaWikiServices;

class CategoryTranscluder {
    public static function onCategoryPageView( $categoryPage ) {
        $output = $categoryPage->getContext()->getOutput();
        $category = $categoryPage->getContext()->getTitle();
        $dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnectionRef( DB_REPLICA );

        // Retrieve pages within the category
        $pageIds = $dbr->select(
            'categorylinks',
            'cl_from',
            [
                'cl_to' => $category->getDBkey()
            ],
            __METHOD__
        );

        $parser = MediaWikiServices::getInstance()->getParser();
        $transcludedContent = '';

        foreach ( $pageIds as $pageId ) {
            $title = Title::newFromID( $pageId->cl_from );
            if ( $title && $title->exists() ) {
                $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
                $revisionRecord = $wikiPage->getRevisionRecord();

                if ( $revisionRecord ) {
                    $content = $revisionRecord->getContent( 'main' );
                    $wikitext = ContentHandler::getContentText( $content );

                    // Parse the wikitext to HTML using the parser with deferred processing
                    $parserOptions = ParserOptions::newFromContext( $output->getContext() );
                    $parserOutput = $parser->parse( $wikitext, $title, $parserOptions );
                }
            }
        }

        // Add the transcluded content to be processed by ParserOutputPostCache
        $output->addParserOutputContent( $parserOutput );

        return true;
    }
}

?>