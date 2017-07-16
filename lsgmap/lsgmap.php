<?php
// on charge le fichier permettant de déclarer la class Lsg_Map_Widget comme fille de la class WP_Widget :
include_once plugin_dir_path( __FILE__ ).'/lsgmapwidget.php';


/**
* Class possédant les fonctionnalités coeur du plugin
*/
class Lsg_Map
{
    public function __construct()
    {
	    /////   WIDGET   /////

	    // au hook "widgets_init" (chargement des widgets), utiliser/enregistrer la class "Lsg_Map_Widget" (définie dans le fichier inclus ci-dessus) en tant que widget (ainsi, WP le comprend dans le TB) :
	    add_action('widgets_init', function()
	    {
	        // enregistrement en tant que widget :
	        register_widget('Lsg_Map_Widget');
	    });


        /////   MENUS   /////

        // LEFTSIDE MENU :
        // Au HOOK "admin_menu" (chargement des menus de WP) : ajouter un élément de 1er niveau dans la barre de menu gauche du TB :
        add_action('admin_menu', array($this, 'lsgmap_leftside_menu'));


        /////   DB   /////

        // INSERTION MARKER DANS DB :
        // Au HOOK "admin_menu" suite au click sur le bouton submit : insertion dans la DB du marker posté :
        add_action('admin_menu', array($this, 'insert_lsgmap_marker'));

        // MODIFICATION MARKER DANS DB :
        add_action('admin_menu', array($this, 'update_lsgmap_marker'));

        // SUPPRESSION MARKER :
        add_action('admin_menu', array($this, 'delete_lsgmap_marker'));

        // SETTINGS/REGLAGES (centre + zoom) :
        add_action('admin_menu', array($this, 'lsgmap_settings'));

    }// end "function __construct()"




    ////////   MENUS   ////////

    // LEFTSIDE MENU :
    // -> Ajouter un élément de 1er niveau dans la barre de menu gauche du TB.
    // On doit définir la fonction dans cette class "Lsg_Map", car cette fonction est appelée à l'instance de cette class (cf. constructeur).
    public function lsgmap_leftside_menu()
    {
        // La création d'un menu s'effectue avec  la fonction "add_menu_page()", qui peut prendre jusqu'à sept paramètres :
        //   -  le titre de la page sur laquelle nous serons redirigés  :       "LSG Map - gestion"
        //   -  le libellé du menu :                                            "LSG Map"
        //   -  l'intitulé des droits que doit posséder l'utilisateur pour pouvoir accéder au menu. Si les droits sont insuffisants, le menu sera masqué :                                     je crois : "manage_options"
        //   -  la clé d'identifiant du menu qui doit être unique (mettre le nom du plugin est une bonne option) :
        //                                                                      "lsgmap"
        //   -  la fonction à appeler pour le rendu de la page pointée par le menu :
        //                                                                      "lsgmap_page_menu"
        //   -  l'icône à utiliser pour le lien (vous pouvez laisser les valeurs par défaut) ;
        //   -  la position dans le menu (vous pouvez laisser les valeurs par défaut).
        add_menu_page('LSG Map - gestion', 'LSG Map', 'manage_options', 'lsgmap', array($this, 'lsgmap_page_menu'));
    }


    // MENU HOMEPAGE :
    // -> ce qui est AFFICHE sur la PAGE d'ACCUEIL du plugin (tableau de bord) :
    public function lsgmap_page_menu()
    {
    ?>
        
        <!-- La fonction "get_admin_page_title()" renvoie la valeur du premier argument donné à la fonction "add_menu_page()" -->
        <h1><?php echo get_admin_page_title(); ?></h1>

        <?php
        // @@@@@@@@@@@@@@@@
        var_dump($_POST);
        // @@@@@@@@@@@@@@@@
        // si l'utilisateur a cliqué sur "Modifier" un marker :
        if ($_POST['submit'] === 'Modifier' && isset($_POST['marker_for_modify']))
        {
        ?>

            <!-- /////   MODIFIER UN MARKER   ///// -->
            <h3>Modifier le marker sélectionné</h3>

                <table class="lsgmap-table">
                    <tr><!-- row d'en-tête -->
                        <th>Type</th><!-- col 1 -->
                        <th>Titre</th><!-- col 2 -->
                        <th title="L'adresse est utilisée pour positionner le marker, sauf si le couple lat-lng est renseigné, auquel cas on prendra de préférence en compte la latitude et la longitude. Néanmoins, une adresse renseignée apparaîtra toujours dans l'infobulle (à titre d'information).">Adresse</th><!-- col 3 -->
                        <th title="Si renseigné, s'appliquera au titre (il faudra donc que le titre soit renseigné)">Lien site web</th><!-- col 4 -->
                        <th>Description</th><!-- col 5 -->
                        <th>Modifier ce marker</th><!-- col 6 -->
                    </tr>

                <?php
                global $wpdb;

                // On rentre en variable la "value" récupérée dans le formulaire (= l'id du marker à modifier) :
                $markerForModify = $_POST['marker_for_modify'];

                // On sélectionne la ligne de ce marker :
                $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}lsgmap_table WHERE id = '$markerForModify'");
                ?>

                    <form method="post" action="">
                    <tr><!-- 2ème row -->
                    
                        <!-- "<td>" : chaque colonne : -->
                        <!-- "$row->type" -> sélection du type au sein de la row -->
                        <td class="col-mtype"><!-- col 1 -->
                            <select id="lsgmap_typemodified" name="lsgmap_typemodified">
                                <option value="<?php echo $row->type; ?>"><?php echo $row->type; ?></option>
                                <option value="skatepark">skatepark</option>
                                <option value="spot">spot</option>
                                <option value="shop">shop</option>
                                <option value="asso">asso</option>
                            </select>
                        </td>
                        
                        <td class="col-mtitle"><!-- col 2 -->
                            <input id="lsgmap_titlemodified" name="lsgmap_titlemodified" type="text" value="<?php echo $row->title; ?>" placeholder="(pas de titre)"/>
                        </td>

                        <td title="L'adresse est utilisée pour positionner le marker, sauf si le couple lat-lng est renseigné, auquel cas on prendra de préférence en compte la latitude et la longitude. Néanmoins, une adresse renseignée apparaîtra toujours dans l'infobulle (à titre d'information)." class="col-maddress"><!-- col 3 -->
                            <textarea id="lsgmap_addressmodified" name="lsgmap_addressmodified"><?php echo $row->address; ?></textarea>
                            <hr/>
                            <div class="text-align-center"><i class="bold">- Alternative de localisation -</i></div>
                            latitude :
                            <br/><input id="lsgmap_latmodified" type="text" name="lsgmap_latmodified" value="<?php echo $row->lat; ?>" placeholder="(latitude non renseignée)"/>
                            longitude :
                            <br/><input id="lsgmap_lngmodified" type="text" name="lsgmap_lngmodified" value="<?php echo $row->lng; ?>" placeholder="(longitude non renseignée)"/>
                        </td>

                        <td title="Si renseigné, s'appliquera au titre (il faudra donc que le titre soit renseigné)" class="col-mlinkhref"><!-- col 4 -->
                            <input id="lsgmap_linkhrefmodified" name="lsgmap_linkhrefmodified" type="url" value="<?php echo $row->linkhref; ?>" placeholder="(pas de lien)"/>
                        </td>

                        <td class="col-mdescription"><!-- col 5 -->
                            <textarea id="lsgmap_descriptionmodified" name="lsgmap_descriptionmodified" placeholder="(pas de description)"><?php echo $row->description; ?></textarea>
                        </td>
                        
                        <td title="Il faut renseigner au moins l'adresse, ou le couple lat-lng, pour que la modification du marker soit validée" class="col-maction"><!-- col 6 -->
                            <input type="hidden" name="marker_modified" value="<?php echo $markerForModify; ?>"/>

                            <?php
                            submit_button( 'Confirmer' );
                            submit_button( 'Annuler', 'button-secondary' );
                            ?>

                        </td>
                        
                    </tr>
                    </form>

                </table>
                <!-- /////   end MODIFIER UN MARKER   ///// -->
            
        <?php
        }// end if "l'utilisateur a cliqué sur "Modifier" un marker"

        // si l'utilisateur n'a pas cliqué sur "Modifier" un marker :
        else
        {
        ?>

            <!-- /////   AJOUT D'UN MARKER   ///// -->
            <h3>Ajouter un marker :</h3>

            <table class="lsgmap-table">

                <tr><!-- row d'en-tête -->
                    <th>Type</th><!-- col 1 -->
                    <th title="Apparaîtra dans l'infobulle du marker">Titre</th><!-- col 2 -->
                    <th title="L'adresse est utilisée pour positionner le marker, sauf si le couple lat-lng est renseigné, auquel cas on prendra de préférence en compte la latitude et la longitude. Néanmoins, une adresse renseignée apparaîtra toujours dans l'infobulle (à titre d'information).">Adresse</th><!-- col 3 -->
                    <th title="Si renseigné, s'appliquera au titre (il faudra donc que le titre soit renseigné)">Lien site web</th><!-- col 4 -->
                    <th title="Apparaîtra dans l'infobulle du marker">Description</th><!-- col 5 -->
                    <th title="Il faut renseigner au moins l'adresse, ou le couple lat-lng, pour que le marker soit ajouté">Ajouter ce marker</th><!-- col 6 -->
                </tr>

                <form method="post" action="">
                <tr><!-- 2ème row -->
                
                    <!-- "<td>" : chaque colonne : -->
                    <td class="col-mtype"><!-- col 1 -->
                        <select id="lsgmap_typeposted" name="lsgmap_typeposted">
                            <option value="skatepark">skatepark</option>
                            <option value="spot">spot</option>
                            <option value="shop">shop</option>
                            <option value="asso">asso</option>
                        </select>
                    </td>
                    
                    <td class="col-mtitle"><!-- col 2 -->
                        <input id="lsgmap_titleposted" name="lsgmap_titleposted" type="text"/>
                    </td>

                    <td title="L'adresse est utilisée pour positionner le marker, sauf si le couple lat-lng est renseigné, auquel cas on prendra de préférence en compte la latitude et la longitude. Néanmoins, une adresse renseignée apparaîtra toujours dans l'infobulle (à titre d'information)." class="col-maddress"><!-- col 3 -->

                        <textarea id="lsgmap_addressposted" name="lsgmap_addressposted"></textarea>
                        <hr/>
                        <div class="text-align-center"><i class="bold">- Alternative de localisation -</i></div>
                        latitude :
                        <br/><input id="lsgmap_latposted" type="text" name="lsgmap_latposted"/>
                        longitude :
                        <br/><input id="lsgmap_lngposted" type="text" name="lsgmap_lngposted"/>
                        
                    </td>

                    <td title="Si renseigné, s'appliquera au titre (il faudra donc que le titre soit renseigné)" class="col-mlinkhref"><!-- col 4 -->
                        <input id="lsgmap_linkhrefposted" name="lsgmap_linkhrefposted" type="url"/>
                    </td>

                    <td class="col-mdescription"><!-- col 5 -->
                        <textarea id="lsgmap_descriptionposted" name="lsgmap_descriptionposted"></textarea>
                    </td>
                    
                    <td title="Il faut renseigner au moins l'adresse, ou le couple lat-lng, pour que le marker soit ajouté" class="col-maction"><!-- col 6 -->
                        <!-- ne sert donc qu'à rafraîchir la page (2ème arg : si le btn est en "primary" ou "secondary") : -->
                        <?php submit_button( 'Ajouter' ); ?>
                    </td>
                    
                </tr>
                </form>


            </table>
            <!-- /////   end AJOUT D'UN MARKER   ///// -->



            <!-- /////   AFFICHAGE TABLEAU LISTE MARKERS   ///// -->
            <br/>
            <hr/>
            <h3>Liste des markers :</h3>

            <table class="lsgmap-table">

                <tr><!-- row d'en-tête -->
                    <th>Type</th><!-- col 1 -->
                    <th>Titre</th><!-- col 2 -->
                    <th>Adresse</th><!-- col 3 -->
                    <th>Lien site web</th><!-- col 4 -->
                    <th>Description</th><!-- col 5 -->
                    <th>Actions</th><!-- col 6 -->
                </tr>

            <?php
            global $wpdb;

            // Sélection des résultats de la table :
            $arrayResults = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}lsgmap_table ORDER BY id DESC") ;
            
            // Dans les résultats de l'ARRAY "$arrayResults" :
            // pour CHAQUE LIGNE (as/en tant que "$row") :
            foreach ($arrayResults as $row)
            {
            ?>

                <tr><!-- une row -->
                
                    <!-- "<td>" : chaque colonne : -->
                    <!-- "$row->type" -> sélection du type au sein de la row -->
                    <td class="col-mtype"><?php echo $row->type; ?></td><!-- col 1 -->
                    <td class="col-mtitle"><?php echo $row->title; ?></td><!-- col 2 -->
                    <td class="col-maddress">

                        <?php echo $row->address; ?>
                        <hr/>
                        latitude : <?php echo $row->lat; ?>
                        <br/>longitude : <?php echo $row->lng; ?>

                    </td><!-- col 3 -->
                    <td class="col-mlinkhref"><?php echo $row->linkhref; ?></td><!-- col 4 -->
                    <td class="col-mdescription"><?php echo $row->description; ?></td><!-- col 5 -->
                    <td class="col-maction"><!-- col 6 -->

                        <!-- inclure le formulaire ici, et non en amont, pour que le bon "value" soit capturé -->
                        <form method="post" action="">

                            <!-- -> renseigner le bon "value" pour que la modification ("$_POST['marker_for_modify']") ou la suppression ("$_POST['marker_deleted']") s'applique sur le bon marker -->
                            <input type="hidden" name="marker_for_modify" value="<?php echo $row->id; ?>"/>
                            <?php submit_button( 'Modifier', 'button-secondary' ); ?>

                            <input type="hidden" name="marker_deleted" value="<?php echo $row->id; ?>"/>
                            <!-- le type "delete" est spécifié ; le rajout de "button-primary" permet de garder un affichage du btn en "primary" (et non en "secondary") -->
                            <?php submit_button( 'Supprimer', 'delete button-primary' ); ?>

                        </form>

                    </td>
                    
                </tr>

            <?php
            }// end "foreach"/pour chaque ligne
            ?>
        
            </table>
            <!-- /////   end AFFICHAGE TABLEAU LISTE MARKERS   ///// -->



            <!-- /////   SETTINGS (center et zoom)   ///// -->
            <br/>
            <hr/>
            <h3>Réglages de la map :</h3>

            <table class="lsgmap-settings-table">

                <form method="post" action="">
                <tr><!-- row d'en-tête -->
                    <th title="Réglage du centre de la carte. Attention à rentrer des valeurs avec un '.' et non une ',' !" colspan="2">CENTRAGE</th><!-- col 1 et 2 -->
                    <th title="Réglage du niveau de zoom : 0 pour un éloignement élevé, 20 pour un rapprochement élevé" colspan="2">ZOOM</th><!-- col 3 et 4 -->
                    <th rowspan="2" title="Si on valide un champ vide, c'est la valeur 0 qui est prise" class="col-setaction">Validation</th><!-- col 5 -->
                </tr>

                <tr><!-- 2ème row -->
                    <td title="Réglage du centre de la carte. Attention à rentrer des valeurs avec un '.' et non une ',' !" class="text-align-center">Latitude</td><!-- col 1 -->
                    <td title="Réglage du centre de la carte. Attention à rentrer des valeurs avec un '.' et non une ',' !" class="text-align-center">Longitude</td><!-- col 2 -->
                    <td title="" class="text-align-center">de la MAP</td><!-- col 4 -->
                    <td title="" class="text-align-center">au clic sur le marker</td><!-- col 5 -->
                </tr>

                <?php
                global $wpdb;

                // On sélectionne la ligne des réglages :
                $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}lsgmap_settings");
                ?>

                <tr><!-- 3ème row -->
                    <td><!-- col 1 -->
                        <input id="lsgmap_centerlatposted" type="text" name="lsgmap_centerlatposted" value="<?php echo $row->centerlat; ?>"/>
                    </td>

                    <td><!-- col 2 -->
                        <input id="lsgmap_centerlngposted" type="text" name="lsgmap_centerlngposted" value="<?php echo $row->centerlng; ?>"/>
                    </td>

                    <td><!-- col 3 -->
                        <!-- (l'ordre "id -> type -> name -> value" semble impacter sur le bon affichage du "value") -->
                        <input id="lsgmap_mapzoomposted" type="number" name="lsgmap_mapzoomposted" value="<?php echo $row->mapzoom; ?>" min="0" max="25"/>
                        <br/>(zoom actuel = <?php echo $row->mapzoom; ?>)
                    </td>

                    <td><!-- col 4 -->
                        <input id="lsgmap_markerzoomposted" type="number" name="lsgmap_markerzoomposted" value="<?php echo $row->markerzoom; ?>" min="0" max="25"/>
                        <br/>(zoom actuel = <?php echo $row->markerzoom; ?>)
                    </td>

                    <td title="Si on valide un champ vide, c'est la valeur 0 qui est prise"><!-- col 5 -->
                        <?php submit_button( 'OK' ); ?>
                    </td>
                </tr>
                </form>

            </table>
            <!-- /////   end SETTINGS   ///// -->

        <?php
        }
        // end else "si l'utilisateur n'a pas cliqué sur "Modifier" un marker"

	}// end "public function lsgmap_page_menu()"




    ////////   DB   ////////

    // CREATION TABLE :
    // (HOOK ACTIVATION du plugin )
    public static function createLsgmapTable()
    {
        // récupération de l'instance de la class wpdb
        // par l'appel à la variable globale stockant cette instance
        global $wpdb;

        // Création de la table
        $wpdb->query(
            // lors de l’installation, WordPress nous avait demandé de choisir un préfixe pour les tables de la base de données. L’attribut prefix de la classe wpdb contient la valeur de ce préfixe
            // (il s'agit ici de wp_)
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lsgmap_table (id INT AUTO_INCREMENT PRIMARY KEY, type VARCHAR(55) NOT NULL, address VARCHAR(255) NOT NULL);"
            );       
    }


    // INSERTION MARKER DANS TABLE :
    public function insert_lsgmap_marker()
    {
        if (
                // Si l'utilisateur a renseigné les champs minimums requis :
                (
                    // S'il existe une variable "lsgmap_addressposted" dans la superglobale $_POST et si la valeur entrée n'est pas VIDE/EMPTY
                    (isset($_POST['lsgmap_addressposted']) && !empty($_POST['lsgmap_addressposted']))
                    ||
                    // ou (ALTERNATIVE à l'adresse) s'il existe les variables "lsgmap_latposted" et "lsgmap_lngposted" et si les valeurs entrées ne sont pas VIDES/EMPTY
                    (isset($_POST['lsgmap_latposted']) && !empty($_POST['lsgmap_latposted']) && isset($_POST['lsgmap_lngposted']) && !empty($_POST['lsgmap_lngposted'])
                    )
                )
                // et si l'utilisateur a cliqué sur 'Ajouter'
                &&
                $_POST['submit'] === 'Ajouter'
            )
        {
            // echo '<script>console.log("Insertion marker à faire")</script>';

            global $wpdb;

            // on rentre en variable ce qui a été posté :
            // ("stripslashes_deep" pour éviter l'insertion de "\" devant certains caractères)
            $typePosted = $_POST['lsgmap_typeposted'];
            $titlePosted = stripslashes_deep($_POST['lsgmap_titleposted']);
            
            // lat : si le champ renseigné est vide, pour éviter d'avoir "0" dans la table (par défaut), on définit la valeur comme "null" :
            if ($_POST['lsgmap_latposted'] === '')
            {
                $latPosted = null;
            }
            else
            {
                $latPosted = $_POST['lsgmap_latposted'];
            }
            // Idem pour la lng :
            if ($_POST['lsgmap_lngposted'] === '')
            {
                $lngPosted = null;
            }
            else
            {
                $lngPosted = $_POST['lsgmap_lngposted'];
            }

            $addressPosted = stripslashes_deep($_POST['lsgmap_addressposted']);
            $linkhrefPosted = stripslashes_deep($_POST['lsgmap_linkhrefposted']);
            $descriptionPosted = stripslashes_deep($_POST['lsgmap_descriptionposted']);

            $wpdb->insert(
                // - Le premier paramètre est le nom de la table dans laquelle on souhaite insérer une ligne,
                "{$wpdb->prefix}lsgmap_table",
                // - le second est un tableau associatif contenant les valeurs de la ligne pour chaque champ de la table.
                array(
                'type' => $typePosted,
                'title' => $titlePosted,
                'lat' => $latPosted,
                'lng' => $lngPosted,
                'address' => $addressPosted,
                'linkhref' => $linkhrefPosted,
                'description' => $descriptionPosted
                )
            );
        }
    }


    // MODIFICATION MARKER DANS TABLE :
    public function update_lsgmap_marker()
    {


        if (
                // Si l'utilisateur a renseigné les champs minimums requis :
                (
                    // S'il existe une variable "lsgmap_addressmodified" dans la superglobale $_POST et si la valeur entrée n'est pas VIDE/EMPTY
                    (isset($_POST['lsgmap_addressmodified']) && !empty($_POST['lsgmap_addressmodified']))
                    ||
                    // ou (ALTERNATIVE à l'adresse) s'il existe les variables "lsgmap_latmodified" et "lsgmap_lngmodified" et si les valeurs entrées ne sont pas VIDES/EMPTY
                    (isset($_POST['lsgmap_latmodified']) && !empty($_POST['lsgmap_latmodified']) && isset($_POST['lsgmap_lngmodified']) && !empty($_POST['lsgmap_lngmodified'])
                    )
                )
                // et si l'utilisateur a cliqué sur 'Confirmer'
                &&
                $_POST['submit'] === 'Confirmer'

            )
        {
            global $wpdb;

            // On rentre en variable la "value" récupérée via le formulaire (= l'id du marker qui va subir la modification) :
            $markerModified = $_POST['marker_modified'];

            // on rentre en variable ce qui a été posté :
            $typeModified = $_POST['lsgmap_typemodified'];
            $titleModified = stripslashes_deep($_POST['lsgmap_titlemodified']);

            // lat : si le champ renseigné est vide, pour éviter d'avoir "0" dans la table (par défaut), on définit la valeur comme "null" :
            if ($_POST['lsgmap_latmodified'] === '')
            {
                $latModified = null;
            }
            else
            {
                $latModified = $_POST['lsgmap_latmodified'];
            }
            // Idem pour la lng :
            if ($_POST['lsgmap_lngmodified'] === '')
            {
                $lngModified = null;
            }
            else
            {
                $lngModified = $_POST['lsgmap_lngmodified'];
            }

            $addressModified = stripslashes_deep($_POST['lsgmap_addressmodified']);
            $linkhrefModified = stripslashes_deep($_POST['lsgmap_linkhrefmodified']);
            $descriptionModified = stripslashes_deep($_POST['lsgmap_descriptionmodified']);

            $wpdb->update(
                // - Le premier paramètre est le nom de la table dans laquelle on souhaite insérer une ligne,
                "{$wpdb->prefix}lsgmap_table",
                // - 2ème paramètre : ce que l'on veut modifier :
                array(
                'type' => $typeModified,
                'title' => $titleModified,
                'lat' => $latModified,
                'lng' => $lngModified,
                'address' => $addressModified,
                'linkhref' => $linkhrefModified,
                'description' => $descriptionModified
                ),
                // - 3ème parmètre : "WHERE" :
                array('id' => $markerModified)
            );
        }
    }


    // SUPPRESSION MARKER DANS TABLE :
    public function delete_lsgmap_marker()
    {
        // S'il existe une variable "lsgsmallads_titleposted" dans la superglobale $_POST
        // et si la valeur entrée n'est pas VIDE/EMPTY :
        if ($_POST['submit'] === 'Supprimer' && isset($_POST['marker_deleted']))
        // if ($_POST['submit'] === 'Ajouter')
        {
            global $wpdb;

            // On rentre en variable la "value" récupérée dans le formulaire (= l'id de l'annonce à valider) :
            $markerDeletedId = $_POST['marker_deleted'];

            $wpdb->delete(
                // - 1er paramètre
                "{$wpdb->prefix}lsgmap_table",
                // - 2ème : "WHERE" : à quelle ligne on veut effectuer ce delete :
                array('id' => $markerDeletedId)
            );
        }      
    }


    // SETTINGS : REGLAGE CENTRE + ZOOM :
    public function lsgmap_settings()
    {
        // Si bien cliqué sur "OK" et que $_POST possède les variables concernées :
        if (isset($_POST['lsgmap_centerlatposted']) && isset($_POST['lsgmap_centerlngposted']) && isset($_POST['lsgmap_mapzoomposted']) && isset($_POST['lsgmap_markerzoomposted']) && $_POST['submit'] === 'OK')
        {
            echo '<script>console.log("Réglages à faire !")</script>';
        
            global $wpdb;

            // on rentre en variable ce qui a été posté :
            $centerlatPosted = $_POST['lsgmap_centerlatposted'];
            $centerlngPosted = $_POST['lsgmap_centerlngposted'];
            $mapzoomPosted = $_POST['lsgmap_mapzoomposted'];
            $markerzoomPosted = $_POST['lsgmap_markerzoomposted'];

            $wpdb->update(
                // - Le premier paramètre est le nom de la table dans laquelle on souhaite insérer une ligne,
                "{$wpdb->prefix}lsgmap_settings",
                // - 2ème paramètre : ce que l'on veut modifier :
                array(
                'centerlat' => $centerlatPosted,
                'centerlng' => $centerlngPosted,
                'mapzoom' => $mapzoomPosted,
                'markerzoom' => $markerzoomPosted
                ),
                // - 3ème parmètre : "WHERE" :
                array('id' => 1)
            );
        }
    }


    // SUPPRESSION TABLE :
    // (HOOK DESINSTALLATION du plugin)
    public static function dropLsgmapTable()
    {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lsgmap_table;");
    }
}// class Lsg_Map


