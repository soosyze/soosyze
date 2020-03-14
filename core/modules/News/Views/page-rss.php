<?xml version="1.0" encoding="iso-8859-1"?><rss version="2.0">
    <channel>
        <title>News</title>
        <link><?php echo $routeRss; ?></link>
        <description></description>
        <language>fr</language>
        <?php foreach ($news as $new): ?>

        <item>
            <title><?php echo htmlspecialchars($new[ 'title' ]); ?></title>
            <link><?php echo $new['route_show']; ?></link>
            <pubDate><?php echo date('D, d M Y H:i:s O', $new[ 'date_created' ]); ?></pubDate>
            <description><?php echo htmlspecialchars($new[ 'field' ][ 'summary' ][ 'field_value' ]); ?></description>
        </item>
        <?php endforeach; ?>

    </channel>
</rss>