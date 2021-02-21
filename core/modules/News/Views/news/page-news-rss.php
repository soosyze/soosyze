<?php echo $xml; ?><rss version="2.0" 
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:slash="http://purl.org/rss/1.0/modules/slash/">

    <channel>
        <title><?php echo htmlspecialchars($title); ?></title>
        <link><?php echo $link; ?></link>
        <description><?php echo htmlspecialchars($description); ?></description>
        <lastBuildDate><?php echo date('D, d M Y H:i:s O', $lastBuildDate); ?></lastBuildDate>
        <language><?php echo $language; ?></language>
        <generator>Soosyze CMS</generator>
        <?php foreach ($items as $item): ?>

        <item>
            <title><?php echo t($item[ 'title' ]); ?></title>
            <link><?php echo $item[ 'link' ]; ?></link>
            <pubDate><?php echo date('D, d M Y H:i:s O', $item[ 'date_created' ]); ?></pubDate>
            <description><?php echo htmlspecialchars($item[ 'field' ][ 'summary' ][ 'field_value' ]); ?></description>
            <content:encoded><![CDATA[<div class="article_img">
                <?php echo $item[ 'field' ][ 'image' ][ 'field_display' ]; ?>
                </div>
                <div class="article_date_time">
                    <?php echo strftime('%d %B %Y', $item[ 'date_created' ]); ?>
                    -
                    ~<?php echo $new[ 'field' ][ 'reading_time' ][ 'field_value' ]
                        . ' '
                        . t(if_or($new[ 'field' ][ 'reading_time' ][ 'field_value' ] === 1, 'minute', 'minutes')); ?>
                </div>
                <?php echo xss($item[ 'field' ][ 'body' ][ 'field_display' ]); ?>

            ]]></content:encoded>
        </item>
        <?php endforeach; ?>

    </channel>
</rss>