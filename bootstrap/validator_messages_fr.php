<?php

return [
    'accepted'                => [
        'must' => 'Le champ :label doit être accepté.',
        'not'  => 'Le champ :label ne doit pas être accepté.'
    ],
    'alpha_num'               => [
        'must' => 'Le champ :label doit contenir que des lettres et des chiffres.',
        'not'  => 'Le champ :label ne doit pas contenir des lettres et des chiffres.'
    ],
    'alpha_num_text'          => [
        'must' => 'Le champ :label doit contenir que des lettres, des chiffres et des caractères de ponctuation.',
        'not'  => 'Le champ :label ne doit pas contenir des lettres, des chiffres et des caractères de ponctuation.'
    ],
    'array'                   => [
        'must' => 'La valeur du champ :label doit être un tableau.',
        'not'  => 'La valeur du :label ne doit pas être un tableau.'
    ],
    'base64'                  => [
        'must' => 'Le champ :label doit être encodé en base64.',
        'not'  => 'Le champ :label ne doit pas être encodé en base64.'
    ],
    'bewteen'                 => [
        'must'         => 'Le champ :label doit être entre :min et :max.',
        'not'          => 'Le champ :label ne doit pas être entre :min et :max.',
        /** Hérite de size */
        'size'         => 'La valeur du champ :label doit être de type entier, flottant, chaine de caractère, tableau, fichier ou ressource.',
    ],
    'bewteen_numeric'         => [
        'must'         => 'Le champ :label doit être entre :min et :max.',
        'not'          => 'Le champ :label ne doit pas être entre :min et :max.',
        /** Hérite de size */
        'size_numeric' => 'La valeur du champ :label doit être numérique.',
    ],
    'bool'                    => [
        'must' => 'La valeur du champ :label doit être un boolean.',
        'not'  => 'La valeur du champ :label ne doit pas être un boolean.'
    ],
    'class_exists'            => [
        'must' => 'Le champ :label doit être une classe instanciable.',
        'not'  => 'Le champ :label ne doit pas être une classe instanciable.'
    ],
    'colorhex'                => [
        'must' => 'Le champ :label doit être une couleur au format hexadecimal.',
        'not'  => 'Le champ :label ne doit pas être une couleur au format hexadecimal.'
    ],
    'date'                    => [
        'must' => 'Le champ :label doit être une date.',
        'not'  => 'Le champ :label ne doit pas être une date.'
    ],
    'date_after'              => [
        'after'     => 'Le champ :label doit être une date supérieur au :dateafter.',
        'not_after' => 'Le champ :label ne doit pas être une date supérieur au :dateafter.',
        /** Hérite de date */
        'must'      => 'Le champ :label doit être une date.',
        'not'       => 'Le champ :label ne doit pas être une date.',
    ],
    'date_after_or_equal'     => [
        'after'     => 'Le champ :label doit être une supérieur ou égale au :dateafter.',
        'not_after' => 'Le champ :label ne doit pas être une date supérieur ou égale au :dateafter.',
        /** Hérite de date */
        'must'      => 'Le champ :label doit être une date.',
        'not'       => 'Le champ :label ne doit pas être une date.',
    ],
    'date_before'             => [
        'before'     => 'Le champ :label doit être une date inférieur au :datebefore.',
        'not_before' => 'Le champ :label ne doit pas être une date inferieur au :datebefore.',
        /** Hérite de date */
        'must'       => 'Le champ :label doit être une date.',
        'not'        => 'Le champ :label ne doit pas être une date.',
    ],
    'date_before_or_equal'    => [
        'before'     => 'Le champ :label doit être une inferieur ou égale au :datebefore.',
        'not_before' => 'Le champ :label ne doit pas être une date inferieur ou égale au :datebefore.',
        /** Hérite de date */
        'must'       => 'Le champ :label doit être une date.',
        'not'        => 'Le champ :label ne doit pas être une date.',
    ],
    'date_format'             => [
        'must' => 'Le champ :label doit être une date au format :format.',
        'not'  => 'Le champ :label ne doit pas être une date au format :format.'
    ],
    'dir'                     => [
        'must' => 'Le champ :label doit être un chemin valide.',
        'not'  => 'Le champ :label ne doit pas être un chemin valide.'
    ],
    'email'                   => [
        'must' => 'Le champ :label doit être une adresse e-mail valide.',
        'not'  => 'Le champ :label ne doit pas être une adresse e-mail valide.'
    ],
    'equal'                   => [
        'must' => 'Le champ :label doit être égale à :value.',
        'not'  => 'Le champ :label ne doit pas être égale à :value.'
    ],
    'equal_strict'            => [
        'must' => 'Le champ :label doit être strictement égale à :value.',
        'not'  => 'Le champ :label ne doit pas être strictement égale à :value.'
    ],
    'file'                    => [
        'must'        => 'Le champ :label n\'est pas un fichier.',
        'not'         => 'Le champ :label ne doit pas être un fichier.',
        'ini_size'    => 'La taille du fichier téléchargé excède la valeur de upload_max_filesize, configurée dans le php.ini',
        'form_size'   => 'La taille du fichier téléchargé excède la valeur de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML.',
        'err_partial' => 'Le fichier n\'a été que partiellement téléchargé.',
        'no_file'     => 'Aucun fichier n\'a été téléchargé.',
        'no_tmp_dir'  => 'Un dossier temporaire est manquant.',
        'cant_write'  => 'Échec de l\'écriture du fichier sur le disque.',
        'extension'   => 'Une extension PHP a arrêté l\'envoi de fichier.'
    ],
    'file_extensions'         => [
        'ext'         => 'Le champ :label doit être un fichier de type : :list.',
        'not_ext'     => 'Le champ :label ne doit pas être un fichier de type : :list.',
        /** Hérite de file */
        'must'        => 'Le champ :label n\'est pas un fichier.',
        'not'         => 'Le champ :label ne doit pas être un fichier.',
        'ini_size'    => 'La taille du fichier téléchargé excède la valeur de upload_max_filesize, configurée dans le php.ini',
        'form_size'   => 'La taille du fichier téléchargé excède la valeur de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML.',
        'err_partial' => 'Le fichier n\'a été que partiellement téléchargé.',
        'no_file'     => 'Aucun fichier n\'a été téléchargé.',
        'no_tmp_dir'  => 'Un dossier temporaire est manquant.',
        'cant_write'  => 'Échec de l\'écriture du fichier sur le disque.',
        'extension'   => 'Une extension PHP a arrêté l\'envoi de fichier.'
    ],
    'file_mimes'              => [
        'mimes'       => 'Le MIME type du fichier :label doit être de type : :list',
        'not_mimes'   => 'Le MIME type du fichier :label ne doit pas être de type : :list',
        /** Hérite de file_extensions */
        'ext'         => 'Le champ :label doit être un fichier de type : :list.',
        'not_ext'     => 'Le champ :label ne doit pas être un fichier de type : :list.',
        /** Hérite de file */
        'must'        => 'Le champ :label n\'est pas un fichier.',
        'not'         => 'Le champ :label ne doit pas être un fichier.',
        'ini_size'    => 'La taille du fichier téléchargé excède la valeur de upload_max_filesize, configurée dans le php.ini',
        'form_size'   => 'La taille du fichier téléchargé excède la valeur de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML.',
        'err_partial' => 'Le fichier n\'a été que partiellement téléchargé.',
        'no_file'     => 'Aucun fichier n\'a été téléchargé.',
        'no_tmp_dir'  => 'Un dossier temporaire est manquant.',
        'cant_write'  => 'Échec de l\'écriture du fichier sur le disque.',
        'extension'   => 'Une extension PHP a arrêté l\'envoi de fichier.'
    ],
    'file_mimetype'           => [
        'mimetype'     => 'Le champ :label doit être un fichier de type : :list.',
        'not_mimetype' => 'Le champ :label ne doit pas être un fichier de type : :list.',
        /** Hérite de file */
        'must'         => 'Le champ :label n\'est pas un fichier.',
        'not'          => 'Le champ :label ne doit pas être un fichier.',
        'ini_size'     => 'La taille du fichier téléchargé excède la valeur de upload_max_filesize, configurée dans le php.ini',
        'form_size'    => 'La taille du fichier téléchargé excède la valeur de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML.',
        'err_partial'  => 'Le fichier n\'a été que partiellement téléchargé.',
        'no_file'      => 'Aucun fichier n\'a été téléchargé.',
        'no_tmp_dir'   => 'Un dossier temporaire est manquant.',
        'cant_write'   => 'Échec de l\'écriture du fichier sur le disque.',
        'extension'    => 'Une extension PHP a arrêté l\'envoi de fichier.'
    ],
    'float'                   => [
        'must' => 'La valeur du champ :label doit être un nombre flottant.',
        'not'  => 'La valeur du champ :label ne doit pas être un nombre flottant.'
    ],
    'fontawesome'             => [
        'must' => 'Le champ :label doit correspondre à l\'un des styles de FontAwesome suivant : :list.',
        'not'  => 'Le champ :label ne doit pas correspondre à l\'un des styles de FontAwesome suivant : :list.'
    ],
    'image'                   => [
        'ext'     => 'Le champ :label doit être un fichier de type : :list.',
        'not_ext' => 'Le champ :label ne doit pas être un fichier de type : :list.'
    ],
    'image_dimensions_height' => [
        'must'         => 'La hauteur de l\'image :label doit être comprise entre :minpx et :maxpx.',
        'not_must'     => 'La hauteur de l\'image :label ne doit pas être comprise entre :minpx et :maxpx.',
        /** Hérite de file_mimetype */
        'mimetype'     => 'Le champ :label doit être un fichier de type : :list.',
        'not_mimetype' => 'Le champ :label ne doit pas être un fichier de type : :list.',
        /** Hérite de file */
        'ini_size'     => 'La taille du fichier téléchargé excède la valeur de upload_max_filesize, configurée dans le php.ini',
        'form_size'    => 'La taille du fichier téléchargé excède la valeur de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML.',
        'err_partial'  => 'Le fichier n\'a été que partiellement téléchargé.',
        'no_file'      => 'Aucun fichier n\'a été téléchargé.',
        'no_tmp_dir'   => 'Un dossier temporaire est manquant.',
        'cant_write'   => 'Échec de l\'écriture du fichier sur le disque.',
        'extension'    => 'Une extension PHP a arrêté l\'envoi de fichier.'
    ],
    'image_dimensions_width'  => [
        'must'         => 'La largeur de l\'image :label doit être comprise entre :minpx et :maxpx.',
        'not_must'     => 'La largeur de l\'image :label ne doit pas être comprise entre :minpx et :maxpx.',
        /** Hérite de file_mimetype */
        'mimetype'     => 'Le champ :label doit être un fichier de type : :list.',
        'not_mimetype' => 'Le champ :label ne doit pas être un fichier de type : :list.',
        /** Hérite de file */
        'ini_size'     => 'La taille du fichier téléchargé excède la valeur de upload_max_filesize, configurée dans le php.ini',
        'form_size'    => 'La taille du fichier téléchargé excède la valeur de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML.',
        'err_partial'  => 'Le fichier n\'a été que partiellement téléchargé.',
        'no_file'      => 'Aucun fichier n\'a été téléchargé.',
        'no_tmp_dir'   => 'Un dossier temporaire est manquant.',
        'cant_write'   => 'Échec de l\'écriture du fichier sur le disque.',
        'extension'    => 'Une extension PHP a arrêté l\'envoi de fichier.'
    ],
    'inarray'                 => [
        'must' => 'Le champ :label doit être dans la liste suivante : :list.',
        'not'  => 'Le champ :label ne doit pas être dans la liste suivante : :list.'
    ],
    'instanceof'              => [
        'must' => 'Le champ :label doit être une instance de :class.',
        'not'  => 'Le champ :label ne doit pas être une instance de :class.'
    ],
    'int'                     => [
        'must' => 'La valeur du champ :label doit être un nombre entier.',
        'not'  => 'La valeur du champ :label ne doit pas être un nombre entier.'
    ],
    'ip'                      => [
        'must' => 'Le champ :label doit être une adresse :version valide.',
        'not'  => 'Le champ :label ne doit pas être une adresse :version valide.'
    ],
    'iterable'                => [
        'must' => 'La valeur du champ :label doit être itérable.',
        'not'  => 'La valeur du champ :label ne doit pas être itérable.'
    ],
    'json'                    => [
        'must' => 'Le champ :label doit être au format JSON.',
        'not'  => 'Le champ :label ne doit pas être au format JSON.'
    ],
    'max'                     => [
        'must' => 'Le champ :label ne doit pas être supérieur à :max.',
        'not'  => 'Le champ :label doit être supérieur à :max.',
        /** Hérite de size */
        'size'         => 'La valeur du champ :label doit être de type entier, flottant, chaine de caractère, tableau, fichier ou ressource.',
    ],
    'max_numeric'             => [
        'must' => 'Le champ :label ne doit pas être supérieur à :max.',
        'not'  => 'Le champ :label doit être supérieur à :max.',
        /** Hérite de size */
        'size_numeric' => 'La valeur du champ :label doit être numérique.',
    ],
    'min'                     => [
        'must' => 'Le champ :label ne doit pas être inférieur à :min.',
        'not'  => 'Le champ :label doit être inférieur à :min.',
        /** Hérite de size */
        'size'         => 'La valeur du champ :label doit être de type entier, flottant, chaine de caractère, tableau, fichier ou ressource.',
    ],
    'min_numeric'             => [
        'must' => 'Le champ :label ne doit pas être inférieur à :min.',
        'not'  => 'Le champ :label doit être inférieur à :min.',
        /** Hérite de size */
        'size_numeric' => 'La valeur du champ :label doit être numérique.',
    ],
    'null'                    => [
        'must' => 'La valeur du champ :label doit être NULL.',
        'not'  => 'La valeur du champ :label ne doit pas être NULL.'
    ],
    'numeric'                 => [
        'must' => 'La valeur du champ :label doit être numérique.',
        'not'  => 'La valeur du champ :label ne doit pas être numérique.'
    ],
    'regex'                   => [
        'must' => 'Le champ :label doit correspond à la règle de validation :regex',
        'not'  => 'Le champ :label ne doit pas correspondre à la règle de validation :regex'
    ],
    'required'                => [
        'must' => 'Le champ :label est requis.'
    ],
    'required_with'           => [
        'must' => 'Le champ :label est requis en présence d\'un des champs suivants : :values.'
    ],
    'required_with_all'       => [
        'must' => 'Le champ :label est requis en présence de tous les champs suivants : :values.'
    ],
    'required_without'        => [
        'must' => 'Le champ :label est requis en l\'absence d\'un des champs suivants : :values.'
    ],
    'required_without_all'    => [
        'must' => 'Le champ :label est requis en l\'absence de tous les champs suivants : :values.'
    ],
    'ressource'               => [
        'must' => 'La valeur du champ :label doit être une ressource.',
        'not'  => 'La valeur du champ :label ne doit pas être une ressource.'
    ],
    'route'                   => [
        'must' => 'La valeur du champ :label doit être une route.',
        'not'  => 'La valeur du champ :label ne doit pas être une route.'
    ],
    'route_or_url'            => [
        'must' => 'La valeur du champ :label doit être un lien ou une route.',
        'not'  => 'La valeur du champ :label ne doit pas être un lien ou une route.'
    ],
    'size'                    => [
        'size'         => 'La valeur du champ :label doit être de type entier, flottant, chaine de caractère, tableau, fichier ou ressource.',
        'size_numeric' => 'La valeur du champ :label doit être numérique.'
    ],
    'slug'                    => [
        'must' => 'Le champ :label doit contenir que des lettres, chiffres, tirets et anderscore.',
        'not'  => 'Le champ :label ne doit pas contenir de lettres, chiffres, tirets et anderscore.'
    ],
    'string'                  => [
        'must' => 'La valeur du champ :label doit être une chaine de caractères.',
        'not'  => 'La valeur du champ :label ne doit pas être une chaine de caractères.'
    ],
    'timezone'                => [
        'must' => 'Le champ :label doit être un fuseau horaire valide.',
        'not'  => 'Le champ :label ne doit pas être un fuseau horaire valide.'
    ],
    'token'                   => [
        'error'   => 'Une erreur est survenue.',
        'invalid' => 'Le token n\'est pas valide.',
        'time'    => 'Vous avez attendu trop longtemps, veuillez recharger la page.'
    ],
    'url'                     => [
        'must' => 'Le champ :label doit être une URL valide.',
        'not'  => 'Le champ :label ne doit pas être une URL valide.'
    ],
    'uuid'                    => [
        'must' => 'Le champ :label doit au format UUID v4.',
        'not'  => 'Le champ :label ne doit pas être au format UUID v4.'
    ],
    'version'                 => [
        'must' => 'Le champ :label doit être une version semantic valide.',
        'not'  => 'Le champ :label ne doit pas être une version semantic valide.'
    ]
];
