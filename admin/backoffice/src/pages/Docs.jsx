import { IconBook, IconCode, IconLayers, IconMapPin, IconStar, IconZap } from '../components/Icons'

function Section({ icon: Icon, title, children }) {
  return (
    <div className="border border-gray-200 bg-white rounded-none">
      <div className="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
        <Icon size={15} strokeWidth={1.5} className="text-gray-500" />
        <h2 className="text-sm font-semibold text-gray-800 uppercase tracking-wide">{title}</h2>
      </div>
      <div className="p-5 space-y-4">{children}</div>
    </div>
  )
}

function Code_({ children, className = '' }) {
  return (
    <code className={`block bg-gray-50 border border-gray-200 rounded px-3 py-2 text-xs font-mono text-gray-700 whitespace-pre-wrap ${className}`}>
      {children}
    </code>
  )
}

function Param({ name, type, desc, def }) {
  return (
    <div className="flex items-start gap-3 py-2 border-b border-gray-50 last:border-0">
      <code className="text-xs font-mono text-black bg-gray-100 px-1.5 py-0.5 rounded shrink-0">{name}</code>
      <span className="text-xs text-blue-600 shrink-0">{type}</span>
      <span className="text-xs text-gray-600 flex-1">{desc}</span>
      {def && <span className="text-xs text-gray-400 shrink-0">défaut: <code className="font-mono">{def}</code></span>}
    </div>
  )
}

export default function Docs() {
  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6">
      <div className="border-b border-gray-200 pb-4">
        <h1 className="text-base tracking-wide uppercase text-gray-800">Documentation</h1>
        <p className="text-xs text-gray-500 mt-1">Référence complète pour utiliser SJ Reviews en frontend.</p>
      </div>

      {/* Shortcode */}
      <Section icon={Code} title="Shortcode [sj_reviews]">
        <p className="text-sm text-gray-600">
          Intégrez vos avis n'importe où dans WordPress (pages, articles, widgets texte).
        </p>
        <Code_>[sj_reviews]</Code_>

        <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-4 mb-2">Exemples rapides</h3>
        <div className="space-y-2">
          <Code_>[sj_reviews layout="slider-i" preset="minimal" max="5"]</Code_>
          <Code_>[sj_reviews layout="badge" preset="dark"]</Code_>
          <Code_>[sj_reviews layout="grid" columns="3" preset="white"]</Code_>
          <Code_>[sj_reviews layout="list" rating_min="4"]</Code_>
          <Code_>[sj_reviews lieu_id="lieu_xxxxxxxx"]</Code_>
          <Code_>[sj_reviews source="google" max="10"]</Code_>
        </div>

        <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-6 mb-2">Paramètres disponibles</h3>
        <div className="border border-gray-200 divide-y divide-gray-100">
          <Param name="layout" type="string" desc="Type d'affichage" def="slider-i" />
          <Param name="preset" type="string" desc="Style visuel : minimal, dark, white" def="minimal" />
          <Param name="max" type="number" desc="Nombre max d'avis à afficher" def="5" />
          <Param name="columns" type="number" desc="Colonnes pour le layout grid (1–4)" def="3" />
          <Param name="lieu_id" type="string" desc="Filtrer par lieu (ex: lieu_a1b2c3d4)" def="" />
          <Param name="source" type="string" desc="Filtrer par source : google, tripadvisor, facebook, trustpilot, direct, autre" def="" />
          <Param name="rating_min" type="number" desc="Note minimum à afficher (0 = tous)" def="0" />
          <Param name="place_id" type="string" desc="Google Place ID pour le lien GMB (badge)" def="" />
          <Param name="show_stars" type="0|1" desc="Afficher les étoiles" def="1" />
          <Param name="show_text" type="0|1" desc="Afficher le texte de l'avis" def="1" />
          <Param name="show_author" type="0|1" desc="Afficher l'auteur" def="1" />
          <Param name="show_date" type="0|1" desc="Afficher la date relative" def="1" />
          <Param name="show_certified" type="0|1" desc="Afficher le badge certifié" def="1" />
          <Param name="show_source" type="0|1" desc="Afficher l'icône de source" def="1" />
          <Param name="title" type="string" desc="Titre de section (vide = aucun)" def="" />
          <Param name="title_tag" type="string" desc="Tag HTML du titre : h2, h3, h4, p" def="h3" />
          <Param name="autoplay" type="0|1" desc="Lecture automatique du slider" def="0" />
          <Param name="autoplay_delay" type="ms" desc="Délai autoplay en millisecondes" def="4000" />
          <Param name="loop" type="0|1" desc="Slider en boucle infinie" def="1" />
          <Param name="speed" type="ms" desc="Vitesse de transition en ms" def="500" />
          <Param name="show_arrows" type="0|1" desc="Flèches de navigation" def="1" />
          <Param name="arrow_style" type="string" desc="Style flèches : chevron, arrow, circle" def="chevron" />
          <Param name="show_dots" type="0|1" desc="Points de pagination" def="1" />
          <Param name="dots_style" type="string" desc="Style dots : bullet, line, number" def="bullet" />
          <Param name="schema" type="0|1" desc="Injection Schema.org JSON-LD (AggregateRating)" def="1" />
          <Param name="star_color" type="hex" desc="Couleur des étoiles" def="#f5a623" />
          <Param name="certified_label" type="string" desc="Texte du badge certifié" def="Certifié" />
        </div>
      </Section>

      {/* Layouts */}
      <Section icon={Layers} title="Layouts disponibles">
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
          {[
            { name: 'slider-i', desc: 'Slider pleine largeur, 1 carte visible' },
            { name: 'slider-ii', desc: 'Sidebar agrégat (33%) + slider (66%)' },
            { name: 'badge', desc: 'Note globale + lien Google — compact' },
            { name: 'grid', desc: 'Grille responsive N colonnes' },
            { name: 'list', desc: 'Liste verticale' },
          ].map(l => (
            <div key={l.name} className="border border-gray-200 p-3">
              <code className="text-xs font-mono text-black font-semibold">{l.name}</code>
              <p className="text-xs text-gray-500 mt-1">{l.desc}</p>
            </div>
          ))}
        </div>
      </Section>

      {/* Lieux */}
      <Section icon={MapPin} title="Multi-lieux">
        <p className="text-sm text-gray-600">
          Chaque lieu dispose d'un identifiant unique. Retrouvez-le dans <strong>Lieux &amp; Sources</strong> en cliquant sur l'icône ↓.
        </p>
        <Code_>{`[sj_reviews lieu_id="lieu_a1b2c3d4"]
[sj_reviews lieu_id="lieu_a1b2c3d4" layout="badge"]
[sj_reviews lieu_id="lieu_a1b2c3d4" source="google" max="5"]`}</Code_>
        <p className="text-xs text-gray-500 mt-2">
          Vous pouvez combiner <code className="font-mono">lieu_id</code> avec n'importe quel autre paramètre.
          Si vous ne spécifiez pas de <code className="font-mono">lieu_id</code>, tous les avis sont affichés.
        </p>
      </Section>

      {/* Elementor */}
      <Section icon={Star} title="Widget Elementor">
        <p className="text-sm text-gray-600">
          Glissez le widget <strong>SJ Reviews</strong> (catégorie StudioJae) dans votre page Elementor.
          Tous les paramètres sont configurables via les onglets <em>Contenu</em> et <em>Style</em>.
        </p>
        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 mt-3">
          {[
            ['Contenu › Source des avis', 'CPT ou ACF, nombre max, note minimum, lieu, source'],
            ['Contenu › Layout', 'Type d\'affichage, colonnes, titre de section'],
            ['Contenu › Contenu carte', 'Éléments visibles sur chaque carte'],
            ['Contenu › Options slider', 'Autoplay, boucle, vitesse, flèches, dots'],
            ['Contenu › Schema.org', 'JSON-LD pour le SEO (AggregateRating)'],
            ['Style › Preset', 'minimal, dark ou white comme base'],
            ['Style › Cartes', 'Normal / Hover : fond, bordure, ombre, translateY, transition'],
            ['Style › Texte & couleurs', 'Étoiles, typographies, couleurs auteur/date'],
          ].map(([titre, detail]) => (
            <div key={titre} className="border border-gray-100 p-3 bg-gray-50">
              <div className="text-xs font-semibold text-gray-700">{titre}</div>
              <div className="text-xs text-gray-500 mt-0.5">{detail}</div>
            </div>
          ))}
        </div>
      </Section>

      {/* Schema.org */}
      <Section icon={Zap} title="Schema.org / SEO">
        <p className="text-sm text-gray-600">
          Le plugin injecte automatiquement un JSON-LD <code className="font-mono text-xs">AggregateRating</code> dans la page,
          activé par défaut. Désactivez-le si vous gérez vous-même le balisage structuré.
        </p>
        <Code_>{`// Shortcode — désactiver le schema
[sj_reviews schema="0"]

// Elementor — onglet Contenu › Schema.org › Désactiver`}</Code_>
        <p className="text-xs text-gray-500">
          Le type d'entité est configurable dans Elementor : LocalBusiness, Product, Service, TouristTrip.
        </p>
      </Section>

      {/* API REST */}
      <Section icon={Book} title="API REST (développeurs)">
        <p className="text-sm text-gray-600">
          Toutes les routes nécessitent le rôle <code className="font-mono text-xs">manage_options</code> et un nonce WP REST valide.
        </p>
        <div className="space-y-2">
          {[
            ['GET',    '/wp-json/sj-reviews/v1/dashboard',              'Stats globales (total, avg, distribution, par source)'],
            ['GET',    '/wp-json/sj-reviews/v1/reviews',                'Liste des avis (filtres: page, per_page, search, rating, source, lieu_id, orderby, order)'],
            ['GET',    '/wp-json/sj-reviews/v1/reviews/{id}',          'Détail d\'un avis'],
            ['POST',   '/wp-json/sj-reviews/v1/reviews',               'Créer un avis'],
            ['PUT',    '/wp-json/sj-reviews/v1/reviews/{id}',          'Modifier un avis'],
            ['DELETE', '/wp-json/sj-reviews/v1/reviews/{id}',          'Supprimer un avis'],
            ['GET',    '/wp-json/sj-reviews/v1/lieux',                  'Liste des lieux (avec avis_count)'],
            ['POST',   '/wp-json/sj-reviews/v1/lieux',                  'Créer un lieu'],
            ['PUT',    '/wp-json/sj-reviews/v1/lieux/{id}',             'Modifier un lieu'],
            ['DELETE', '/wp-json/sj-reviews/v1/lieux/{id}',             'Supprimer un lieu'],
            ['POST',   '/wp-json/sj-reviews/v1/lieux/{id}/sync-google', 'Importer avis depuis Google Places (5 min rate limit)'],
            ['GET',    '/wp-json/sj-reviews/v1/settings',               'Lire les réglages'],
            ['POST',   '/wp-json/sj-reviews/v1/settings',               'Enregistrer les réglages'],
          ].map(([method, route, desc]) => (
            <div key={route} className="flex items-start gap-2 text-xs">
              <span className={`shrink-0 font-mono font-bold w-14 text-right ${
                method === 'GET' ? 'text-emerald-600' : method === 'POST' ? 'text-blue-600' : method === 'PUT' ? 'text-orange-500' : 'text-red-500'
              }`}>{method}</span>
              <code className="font-mono text-gray-700 shrink-0 w-96 truncate">{route}</code>
              <span className="text-gray-500">{desc}</span>
            </div>
          ))}
        </div>
      </Section>
    </div>
  )
}
