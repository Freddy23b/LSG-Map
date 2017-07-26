<?php
/**
* Class WIDGET permettant de définir le widget intégrant notre plugin :
*/

// Tous les widgets sont des objets qui doivent hériter de la classe WP_Widget :
class Lsg_Map_Widget extends WP_Widget
{
    public function __construct()
    {
        // 1ERE METHOD A SURCHARGER : AFFICHAGE du widget dans le TB :
        // Avant tout, le constructeur de la classe WP_Widget doit être appelé par le constructeur de la classe fille afin de définir :
        // > un identifiant pour le widget ;
        // > un titre à afficher dans l’administration ;
        // > éventuellement des paramètres supplémentaires comme la description du widget (elle aussi affichée dans le panneau « widget » de l’administration)        
        parent::__construct('lsg_map_widget_id', 'LSG Map widget', array('description' => 'Une map Les SALLES gosses personnalisée. Shortcode associé : [lsgmap_shortcode]'));


        // Déclarer le SHORTCODE du widget :
        // > 1er param : nom du shortcode
        // > 2ème param : fonction à appeler lors de son rendu
        add_shortcode('lsgmap_shortcode', array($this, 'widget'));
    }

    
    // 2EME METHOD A SURCHARGER : AFFICHAGE du widget dans le DOM :
    public function widget()
    {
    ?>


        <h3 class="lsgmap-h3">L'ASSO et autour : skateparks, shops...</h3>

        <div class="lsgmap-divtocenter">

            <div class="lsgmap-div">

                <!-- la div contenant la map : -->
                <div id="map" class="lsgmap-map-div"></div>

                <!-- Div comportant la legend : -->   
                <div class="lsgmap-legend-div">

                    <h4>LEGENDE</h4>

                    <div class="lsg-legend-elements-div">

                        <div>
                            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>img/mapicon-lsg.png" alt="logo Les SALLES gosses"/>
                            <span>L'Asso</span>
                        </div>
                    
                        <div>
                            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>img/mapicon-skate.png" alt="icône skate"/>
                            <span>Skatepark</span>
                        </div>
                        
                        <div>
                            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>img/mapicon-spot.png" alt="icône spot"/>
                            <span>Spot</span>
                        </div>

                        <div>
                            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>img/mapicon-shop.png" alt="icône shop"/>
                            <span>Shop</span>
                        </div>

                    </div>

                </div><!-- lsgmap-legend-div -->

            </div><!-- lsgmap-div -->
                
        </div><!-- lsgmap-divtocenter -->


<?php
/////   DB CONNECTION   /////

// appel à la DB WordPress :
global $wpdb;
// var_dump($wpdb);


/////   SELECTION DES DONNEES   /////

// > REGLAGES GENERAUX MAP :
// Sélectionner la ligne des réglages :
$setRow = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}lsgmap_settings WHERE 1") ;


// > AFFICHAGE MARKERS :
// Sélectionner toutes les lignes (tous les markers) de la table :
$resultArray = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}lsgmap_table WHERE 1") ;

foreach ($resultArray as $markerRow)
{
    // (le terme "marker" est ici important pour que JS le reconnaisse)
    echo '<marker ';
    echo 'type="' . $markerRow->type . '" ';
    echo 'lat="' . $markerRow->lat . '" ';
    echo 'lng="' . $markerRow->lng . '" ';
    echo 'address="' . $markerRow->address . '" ';
    echo 'title="' . $markerRow->title . '" ';
    echo 'linkhref="' . $markerRow->linkhref . '" ';
    echo 'description="' . $markerRow->description . '" ';
    echo '/>';
}
?>


<script>
////////////////////////////////////////////////////////////////
// 
//                   SCRIPT GOOGLE MAP JS
//                       [  PLAN  ]
// 
////////// I  - DEFINITION variables/arrays/objects
////////        A - "customIcons"
////////        B - "lsgmapSettings"
// 
////////// II - INITMAP
////////        A - CREATION map
////////        B - REGLAGES map (center et zooms)
////////        C - DEFINITION MARKERS + INFOWINDOWS
//////              1 - RECUPERATION (GET) des attributs des markers
//////              2 - CONSTRUCTION CONTENU INFOWINDOWS
//////              3 - POSITIONNEMENT + AJOUT des MARKERS
////                    a - Markers avec LAT ET LNG renseignés
//                          i   - ITINERAIRE
//                          ii  - AJOUT markers
//                          iii - EVENEMENTS au CLIC
////                    b - Markers avec LAT ET LNG non renseignés
//                          i   - ITINERAIRE
//                          ii  - GEOCODAGE ADRESSE
//                          iii - AJOUT markers
//                          iv  - EVENEMENTS au CLIC
// 
// 
////////////////////////////////////////////////////////////////


////////// I - DEFINITION variables/arrays/objects

//////// A - "customIcons"

// mettre en variable le chemin ouvrant le DOSSIER PLUGINS (comportant les icons) :
var iconNodeUrl = '<?php echo plugin_dir_url( __FILE__ ) . 'img/'; ?>';

// ARRAY définissant, en fonction du "type" du marker :
// - le "iconLegend" qui sera affiché dans la légende
// - le "iconPicture" qui spécifie la terminologie url pour l'affichage de l'image
var customIcons =
{
    asso:
    {
        iconLegend: 'Les SALLES gosses',
        iconPicture: iconNodeUrl + 'mapicon-lsg.png'
    },
    skatepark:
    {
        iconLegend: 'Skatepark',
        iconPicture: iconNodeUrl + 'mapicon-skate.png'
    },
    shop:
    {
        iconLegend: 'Shop',
        iconPicture: iconNodeUrl + 'mapicon-shop.png'
    },
    spot:
    {
        iconLegend: 'Spot',
        iconPicture: iconNodeUrl + 'mapicon-spot.png'
    }
};
//////// A


//////// B - "lsgmapSettings"

var lsgmapSettings =
{
    centerLatlng:
    {
        lat: 0,
        lng: 0
    },
    mapZoom: 0,
    markerZoom: 0
};
//////// B
////////// I


////////// II - INITMAP

// La fonction initMap initialise et ajoute la carte lors du chargement de la page Web.
function initMap()
{
    //////// A - CREATION MAP

    // "new google.maps.Map()" : nouvel objet Google Maps créant la map (la classe JavaScript qui représente une carte est la classe "Map" ; on entre dans le constructeur de cette class la div dans laquelle on veut intégrer la carte)
        // "getElementById('map')" : la map s'inclura dans la div avec l'id "map"

    var map = new google.maps.Map(document.getElementById('map'),
    {
        // La propriété center indique à l'API où centrer la carte
        center: lsgmapSettings.centerLatlng,
        // La propriété zoom spécifie le niveau de zoom de la carte.
        zoom: lsgmapSettings.mapZoom

    });
    //////// A


    //////// B - REGLAGES map (center et zooms)

    // Récupération dans l'objet JS spécifié des valeurs saisies dans le DB :
    // elles définiront les réglages qui fonctionneront via JS
    
    // > GET : CENTRAGE : LAT & LNG :
    // ("parseFloat" pour une bonne lecture du type double ou float)
    lsgmapSettings.centerLatlng.lat = parseFloat(<?php echo $setRow->centerlat; ?>);
    lsgmapSettings.centerLatlng.lng = parseFloat(<?php echo $setRow->centerlng; ?>);

    // > GET : niveau de ZOOM DE LA MAP :
    // ("parseInt" pour une bonne lecture/analyse du type tinyint)
    lsgmapSettings.mapZoom = parseInt(<?php echo $setRow->mapzoom; ?>);

    // > GET : niveau de ZOOM AU CLIC SUR LE MARKER :
    lsgmapSettings.markerZoom = parseInt(<?php echo $setRow->markerzoom; ?>);
    // console.log(lsgmapSettings);

    // REGLAGE du CENTRAGE (LAT & LNG) et du ZOOM DE LA MAP de la map :
    // (le niveau de zoom au clic "markerZoom" sera utilisé plus bas)
    map.setCenter(lsgmapSettings.centerLatlng);
    map.setZoom(lsgmapSettings.mapZoom);

    //////// B


    //////// C - DEFINITION MARKERS + INFOWINDOWS

    // Création objet "google.maps.Geocoder" (à ce stade, il est encore vide) :
    var geocoder = new google.maps.Geocoder;

    // Création objet "google.maps.InfoWindow" (infowindow 1/3) :
    var infoWindow = new google.maps.InfoWindow;

    // JS va saisir les balises "marker" créées suite à l'appel à la DB pour les rentrer en variable :
    var markers = document.getElementsByTagName('marker');
        
    // pour chaque "marker" présent dans "markers" :
    Array.prototype.forEach.call(markers, function(markerElem)
    {

        ////// 1 - RECUPERATION (GET) des attributs des balises "marker"

        // > GET : l'ICONE => type du marker :
        var type = markerElem.getAttribute('type');

        // > GET : pour le POSITIONNEMENT :
        // - couple lat-lng :
        var markerLat = parseFloat(markerElem.getAttribute('lat'));
        var markerLng = parseFloat(markerElem.getAttribute('lng'));
        // - l'adresse :
        var markerAddress = markerElem.getAttribute('address');

        // > GET : pour le CONTENU de l'INFOWINDOW :
        // - titre dans l'infowindow :
        var title = markerElem.getAttribute('title');
        // - lien/link :
        var linkHref = markerElem.getAttribute('linkhref');
        // - description :
        var description = markerElem.getAttribute('description');

        ////// 1


        ////// 2 - CONSTRUCTION CONTENU INFOWINDOWS
        // (infowindow 2/3)

        // On créé la DIV principale dans l'infowindow :
        var infowincontent = document.createElement('div');
        // On attribue une class à cette div infowindow, pour le CSS :
        infowincontent.className = 'lsgmap-infowindow-div'
        
        // Contenu de cette DIV :

        //// a - TITRE en gras (et éventuellement "en lien") dans l'infowindow :

        // on définit la variable mettant le texte en gras :
        var strong = document.createElement('strong');

        // si un lien est renseigné (si ce lien n'est ni null ni un string vide), on peut l'associer au titre :
        if (linkHref !== null && linkHref !== '')
        {
            // On créé notre variable lien/link :
            var link = document.createElement('a');

            // Le lien s'appliquera sur :
            link.textContent = title
            // Afin d'ouvrir le lien dans un nouvel onglet :
            link.target = '_blank'
            // L'adresse/url (href) du lien :
            link.href = linkHref
            // on applique ce lien à la balise en gras :
            strong.appendChild(link);
            // on applique cette balise (lien + en gras) à la div principale de l'infowindow :
            infowincontent.appendChild(strong);
        }
        else// s'il n'y a pas de lien renseigné
        {
            // le texte en gras s'appliquera sur :
            strong.textContent = title
            // on applique à l'infowindow :
            infowincontent.appendChild(strong);            
        }

        // on va à la ligne :
        infowincontent.appendChild(document.createElement('br'));

        //// a

        //// b - ADRESSE :

        // on définit la variable mettant le texte en non-gras :
        var text = document.createElement('text');
        // on l'applique à l'adresse (apparaît en-dessous du titre) :
        text.textContent = markerAddress
        // on applique à l'infowindow :
        infowincontent.appendChild(text);

        // on sépare et on va à la ligne avec "hr" :
        infowincontent.appendChild(document.createElement('hr'));

        //// b

        //// c - DESCRIPTION :

        // idem :
        var text = document.createElement('text');
        // idem :
        text.textContent = description
        // idem :
        infowincontent.appendChild(text);

        // on va à la ligne :
        infowincontent.appendChild(document.createElement('br'));

        //// c

        //// d - ITINERAIRE (1/2) :

        // le lien vers l'interface itinéraire Google Maps :
        var googleMapItinerary = document.createElement('a');

        // Contenu texte du lien :
        googleMapItinerary.textContent = 'itinéraire'
        // Afin d'ouvrir le lien dans un nouvel onglet :
        googleMapItinerary.target = '_blank'

        //// d
        ////// 2


        ////// 3 - POSITIONNEMENT + AJOUT des MARKERS

        // Pour chaque ligne/marker dans la DB :
        // création d'un objet "customIcon" comprenant l'image ("iconPicture") et le titre-légende ("iconLegend") de l'icon (cf. l'array "customIcons" défini plus haut), définis en fonction du "type" renseigné dans la DB :
        var customIcon = customIcons[type] || {};

        //// a - Markers avec LAT ET LNG renseignés

        // Si aucune des 2 valeurs du couple lat-lng n'est "NaN" (=> "null" dans la DB), alors on utilise la lat et la lng pour le positionnement du marker :
        if (!isNaN(markerLat) && !isNaN(markerLng))
        {
            // i - ITINERAIRE (2/2) :

            // L'url (href) du lien : ici on renseigne le couple LAT-LNG :
            // - 1ère partie avant "/@": center de la carte
            // - 2ème après "/@" : emplacement du marker d'arrivée
            // - la fin précise le niveau de zoom et la langue
            googleMapItinerary.href = "https://www.google.fr/maps/dir//" + markerLat + "," + markerLng + "/@" + markerLat + "," + markerLng + ",16z?hl=fr"

            // on applique cette balise à la div principale de l'infowindow :
            infowincontent.appendChild(googleMapItinerary);
            // i


            // ii - AJOUT MARKERS

            // la variable objet définissant la POSITION des markers :
            var markerLatLng = new google.maps.LatLng(
                markerLat,
                markerLng
            );

            var marker = new google.maps.Marker(
            {
                map: map,
                position: markerLatLng,
                icon: customIcon.iconPicture
            });
            // ii


            // iii - EVENEMENTS AU CLIC

            marker.addListener('click', function()
            {
                // Centrage + zoom au clic :
                map.setCenter(markerLatLng);
                map.setZoom(lsgmapSettings.markerZoom);

                // Après avoir construit la chaîne HTML, le code ci-dessous ajoute au marqueur un écouteur d'événement qui affiche une fenêtre d'info lorsque l'utilisateur clique sur le marqueur.
                // (infowindow 3/3)
                infoWindow.setContent(infowincontent);
                infoWindow.open(map, marker);
            });
            // iii

        }// end - "if (!isNaN(markerLat) && !isNaN(markerLng))"

        //// a


        //// b - Markers avec LAT ET LNG NON RENSEIGNES

        // Si au moins 1 valeur rentrée du couple lat-lng est "NaN" (=> "null" dans la DB) :
        // => il faut utiliser l'ADRESSE du marker à GEOCODER
        else
        {
            // i - ITINERAIRE (2/2) :

            // L'url (href) du lien : ici on renseigne l'ADRESSE :
            googleMapItinerary.href = "https://www.google.fr/maps/dir//" + markerAddress

            // on applique cette balise à la div principale de l'infowindow :
            infowincontent.appendChild(googleMapItinerary);
            // i


            // ii - GEOCODAGE ADRESSE : adresse --> latlng
            
            // on lance la fonction pour géocoder l'adresse rentrée :
            geocodeAddress(geocoder, map);

            function geocodeAddress(geocoder, resultsMap)
            {
                // On a la variable "markerAddress" :
                // on la rentre en argument dans la fonction/method "geocode()" (de l'objet "geocoder" instancié plus haut) :
                geocoder.geocode({'address': markerAddress}, function(results, status)
                {
                    // à ce stade, les coordonnées "results[0].geometry.location" (résultant du géocodage) sont définies.

                    if (status === 'OK')
                    {
                    // ii

                        // iii - AJOUT MARKERS

                        var marker = new google.maps.Marker(
                        {
                            map: map,
                            position: results[0].geometry.location,
                            icon: customIcon.iconPicture
                        });
                        // iii


                        // iV - EVENEMENTS AU CLIC

                        marker.addListener('click', function()
                        {
                            // Centrage + zoom au clic :
                            map.setCenter(results[0].geometry.location);
                            map.setZoom(lsgmapSettings.markerZoom);

                            // (infowindow 3/3)
                            infoWindow.setContent(infowincontent);
                            infoWindow.open(map, marker);
                        });
                        // iV

                    }
                    else// !if (status === 'OK')
                    {
                        alert('Geocode was not successful for the following reason: ' + status);
                    }

                });// end - "geocoder.geocode()"

            }// end - function "geocodeAddress()"

        }// end - !"if (!isNaN(markerLat) && !isNaN(markerLng))"

        //// b
        ////// 3
        
    });// end - "Array.prototype.forEach.call()"

    //////// C
}
////////// II
</script>


<!-- L'élément script charge l'API à partir de l'URL spécifiée. -->
<!-- L'attribut async autorise le navigateur à continuer à rendre le reste de votre page pendant le chargement de l'API.  -->
<!-- Le paramètre callback exécute la fonction initMap une fois que l'API est complètement chargée.  -->
<script async defer
 src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD6zQUtk134e-AZOCVgbzXLfJOLZzRpNVs&callback=initMap">


////////////////////////////////////////////////////////////////
//                END - SCRIPT GOOGLE MAP JS                  //
////////////////////////////////////////////////////////////////
</script>


    <?php
    }// end "public function widget()"
}// end "class Lsg_Map_Widget extends WP_Widget"
