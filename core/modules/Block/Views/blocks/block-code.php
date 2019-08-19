
<pre class="language-php">
<code class="language-php">public function index( $req ){
    /* Notre contenu. Ici une chaîne, mais il aurait pu s’agir d’un tableau. */
    $content  = "Affichage de la liste";
    /* Nous ajoutons notre contenu encodé en JSON dans un flux. */
    $stream   = new \Soosyze\Components\Http\Stream(json_encode($content));
    /* Nous passons le flux à la réponse. */
    $response = new \Soosyze\Components\Http\Response(200, $stream);

    /* Nous retournons la réponse en spécifiant que le type de contenu est du JSON. */
    return $response->withHeader('content-type', 'application/json');
}</code>
</pre>