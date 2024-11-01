<?php
use MediaWiki\MediaWikiServices;
class CategoryTranscluder {
    public static function onCategoryPageView( $categoryPage ) {
        $output = $categoryPage->getContext()->getOutput();
        $category = $categoryPage->getContext()->getTitle();
        // Get the database connection via dependency injection
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
        // Loop through each page and transclude its content
        $transcludedContent = '';
        foreach ( $pageIds as $pageId ) {
            $title = Title::newFromID( $pageId->cl_from );
            if ( $title && $title->exists() ) {
                $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
                $revisionRecord = $wikiPage->getRevisionRecord();
                if ( $revisionRecord ) {
                    // Fetch content from the main slot
                    $content = $revisionRecord->getContent( 'main' );
                    $wikitext = ContentHandler::getContentText( $content );
                    // Parse the wikitext into HTML
                    $parsedContent = $output->parseAsContent( $wikitext );
                    
                    // Add parsed content with a header for each page
                    $transcludedContent .= "<h1>" . htmlspecialchars( $title->getText() ) . "</h1>";
                    $transcludedContent .= $parsedContent;
                }
            }
        }
        // Add the transcluded content to the category page output
        $output->addHTML( '<div class="category-transclusions">' . $transcludedContent . '</div>' );
        return true;
    }
}
?>