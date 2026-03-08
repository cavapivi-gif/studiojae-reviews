import { useState, useEffect } from 'react'
import { api } from '../lib/api'
import { IconBook, IconCode, IconLayers, IconMapPin, IconStar, IconZap } from '../components/Icons'

function Section({ icon: Icon, title, children }) {
  return (
    <div className="border border-gray-200 bg-white">
      <div className="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
        <Icon size={15} strokeWidth={1.5} />
        <h2 className="text-sm font-semibold text-gray-800 uppercase tracking-wide">{title}</h2>
      </div>
      <div className="p-5 space-y-4">{children}</div>
    </div>
  )
}

function CodeBlock({ children }) {
  return (
    <code className="block bg-gray-50 border border-gray-200 px-3 py-2 text-xs font-mono text-gray-700 whitespace-pre-wrap">
      {children}
    </code>
  )
}

function Param({ name, type, desc, def }) {
  return (
    <div className="flex items-start gap-3 py-2 border-b border-gray-50 last:border-0">
      <code className="text-xs font-mono text-black bg-gray-100 px-1.5 py-0.5 shrink-0">{name}</code>
      <span className="text-xs text-blue-600 shrink-0">{type}</span>
      <span className="text-xs text-gray-600 flex-1">{desc}</span>
      {def && <span className="text-xs text-gray-400 shrink-0">défaut: <code className="font-mono">{def}</code></span>}
    </div>
  )
}

export default function Docs() {
  const [lieux, setLieux] = useState([])

  useEffect(() => {
    api.lieux().then(setLieux).catch(() => {})
  }, [])

  const exLieu = lieux.find(l => l.active) ?? lieux[0]
  const exId   = exLieu?.id ?? 'lieu_xxxxxxxx'

  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6">
      <div className="border-b border-gray-200 pb-4">
        <h1 className="text-base tracking-wide uppercase text-gray-800">Documentation</h1>
        <p className="text-xs text-gray-500 mt-1">
          Référence pour utiliser SJ Reviews en frontend.
          {lieux.length > 0 && (
            <span className="ml-2 text-green-600 font-semibold">
              {lieux.length} lieu{lieux.length > 1 ? 'x' : ''} configuré{lieux.length > 1 ? 's' : ''} — les exemples utilisent vos vrais IDs.
            </span>
          )}
        </p>
      </div>

      {/* Shortcode */}
      <Section icon={IconCode} title="Shortcode [sj_reviews]">
        <p className="text-sm text-gray-600">
          Intégrez vos avis n'importe où dans WordPress (pages, articles, widgets texte).
        </p>
        <CodeBlock>[sj_reviews]</CodeBlock>

        <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-4 mb-2">Exemples rapides</h3>
        <div className="space-y-2">
          <CodeBlock>[sj_reviews layout="slider-i" preset="minimal" max="5"]</CodeBlock>
          <CodeBlock>[sj_reviews layout="badge" preset="dark"]</CodeBlock>
          <CodeBlock>[sj_reviews layout="grid" columns="3" preset="white"]</CodeBlock>
          <CodeBlock>[sj_reviews layout="list" rating_min="4"]</CodeBlock>
          <CodeBlock>{`[sj_reviews lieu_id="${exId}"]`}</CodeBlock>
          <CodeBlock>[sj_reviews source="google" max="10"]</CodeBlock>
        </div>

        {lieux.length > 0 && (
          <>
            <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-6 mb-2">
              Vos lieux ({lieux.length})
            </h3>
            <div className="space-y-1">
              {lieux.map(l => (
                <div key={l.id} className="flex items-center gap-3 text-xs py-1.5 border-b border-gray-50">
                  <code className="font-mono text-gray-700 bg-gray-100 px-2 py-0.5">{l.id}</code>
                  <span className="text-gray-700 font-medium">{l.name}</span>
                  <span className="text-gray-400">{l.source}</span>
                  {l.avis_count > 0 && <span className="text-gray-400">{l.avis_count} avis</span>}
                  {!l.active && <span className="text-orange-500">inactif</span>}
                </div>
              ))}
            </div>
            <div className="mt-3 space-y-1">
              {lieux.map(l => (
                <CodeBlock key={l.id}>{`[sj_reviews lieu_id="${l.id}" layout="slider-i"]`}</CodeBlock>
              ))}
            </div>
          </>
        )}

        <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wide mt-6 mb-2">Paramètres disponibles</h3>
        <div className="border border-gray-200 divide-y divide-gray-100">
          <Param name="layout"          type="string"  desc="Type d'affichage" def="slider-i" />
          <Param name="preset"          type="string"  desc="Style visuel : minimal, dark, white" def="minimal" />
          <Param name="max"             type="number"  desc="Nombre max d'avis à afficher" def="5" />
          <Param name="columns"         type="number"  desc="Colonnes pour le layout grid (1–4)" def="3" />
          <Param name="lieu_id"         type="string"  desc="Filtrer par lieu (ex: lieu_a1b2c3d4)" def="" />
          <Param name="source"          type="string"  desc="Filtrer par source : google, tripadvisor, facebook, trustpilot, direct, autre" def="" />
          <Param name="rating_min"      type="number"  desc="Note minimum à afficher (0 = tous)" def="0" />
          <Param name="place_id"        type="string"  desc="Google Place ID pour le lien GMB (badge)" def="" />
          <Param name="show_stars"      type="0|1"     desc="Afficher les étoiles" def="1" />
          <Param name="show_text"       type="0|1"     desc="Afficher le texte de l'avis" def="1" />
          <Param name="show_author"     type="0|1"     desc="Afficher l'auteur" def="1" />
          <Param name="show_date"       type="0|1"     desc="Afficher la date relative" def="1" />
          <Param name="show_certified"  type="0|1"     desc="Afficher le badge certifié" def="1" />
          <Param name="show_source"     type="0|1"     desc="Afficher l'icône de source" def="1" />
          <Param name="title"           type="string"  desc="Titre de section (vide = aucun)" def="" />
          <Param name="title_tag"       type="string"  desc="Tag HTML du titre : h2, h3, h4, p" def="h3" />
          <Param name="autoplay"        type="0|1"     desc="Lecture automatique du slider" def="0" />
          <Param name="autoplay_delay"  type="ms"      desc="Délai autoplay en millisecondes" def="4000" />
          <Param name="loop"            type="0|1"     desc="Slider en boucle infinie" def="1" />
          <Param name="speed"           type="ms"      desc="Vitesse de transition en ms" def="500" />
          <Param name="show_arrows"     type="0|1"     desc="Flèches de navigation" def="1" />
          <Param name="arrow_style"     type="string"  desc="Style flèches : chevron, arrow, circle" def="chevron" />
          <Param name="show_dots"       type="0|1"     desc="Points de pagination" def="1" />
          <Param name="dots_style"      type="string"  desc="Style dots : bullet, line, number" def="bullet" />
          <Param name="schema"          type="0|1"     desc="Injection Schema.org JSON-LD (AggregateRating)" def="1" />
          <Param name="star_color"      type="hex"     desc="Couleur des étoiles" def="#f5a623" />
          <Param name="certified_label" type="string"  desc="Texte du badge certifié" def="Certifié" />
        </div>
      </Section>

      {/* Layouts */}
      <Section icon={IconLayers} title="Layouts disponibles">
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
          {[
            { name: 'slider-i',  desc: 'Slider pleine largeur, 1 carte visible' },
            { name: 'slider-ii', desc: 'Sidebar agrégat (33%) + slider (66%)' },
            { name: 'badge',     desc: 'Note globale + lien Google — compact' },
            { name: 'grid',      desc: 'Grille responsive N colonnes' },
            { name: 'list',      desc: 'Liste verticale' },
          ].map(l => (
            <div key={l.name} className="border border-gray-200 p-3">
              <code className="text-xs font-mono text-black font-semibold">{l.name}</code>
              <p className="text-xs text-gray-500 mt-1">{l.desc}</p>
            </div>
          ))}
        </div>
      </Section>

      {/* Lieux */}
      <Section icon={IconMapPin} title="Multi-lieux">
        <p className="text-sm text-gray-600">
          Chaque lieu a un identifiant unique. Retrouvez-le dans <strong>Lieux &amp; Sources</strong> → icône ↓.
        </p>
        <CodeBlock>{`[sj_reviews lieu_id="${exId}"]
[sj_reviews lieu_id="${exId}" layout="badge"]
[sj_reviews lieu_id="${exId}" source="google" max="5"]`}</CodeBlock>
        <p className="text-xs text-gray-500">
          Combinez <code className="font-mono">lieu_id</code> avec n'importe quel paramètre.
          Sans <code className="font-mono">lieu_id</code>, tous les avis sont affichés.
        </p>
      </Section>

      {/* Elementor */}
      <Section icon={IconStar} title="Widget Elementor">
        <p className="text-sm text-gray-600">
          Glissez le widget <strong>SJ Reviews</strong> (catégorie StudioJae) dans votre page Elementor.
        </p>
        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 mt-3">
          {[
            ['Contenu › Source',         'CPT ou ACF, nombre max, note min, lieu, source'],
            ['Contenu › Layout',         'Type d\'affichage, colonnes, titre'],
            ['Contenu › Carte',          'Éléments visibles sur chaque carte'],
            ['Contenu › Slider',         'Autoplay, boucle, vitesse, flèches, dots'],
            ['Contenu › Schema.org',     'JSON-LD pour le SEO (AggregateRating)'],
            ['Style › Preset',           'minimal, dark ou white comme base'],
            ['Style › Cartes Normal/Hover', 'Fond, bordure, ombre, translateY, transition'],
            ['Style › Texte & couleurs', 'Étoiles, typos, couleurs auteur/date'],
          ].map(([titre, detail]) => (
            <div key={titre} className="border border-gray-100 p-3 bg-gray-50">
              <div className="text-xs font-semibold text-gray-700">{titre}</div>
              <div className="text-xs text-gray-500 mt-0.5">{detail}</div>
            </div>
          ))}
        </div>
      </Section>

      {/* Schema.org */}
      <Section icon={IconZap} title="Schema.org / SEO">
        <p className="text-sm text-gray-600">
          JSON-LD <code className="font-mono text-xs">AggregateRating</code> injecté automatiquement, activé par défaut.
        </p>
        <CodeBlock>{`// Désactiver via shortcode
[sj_reviews schema="0"]

// Désactiver via Elementor — Contenu › Schema.org`}</CodeBlock>
      </Section>

      {/* API REST */}
      <Section icon={IconBook} title="API REST (développeurs)">
        <p className="text-sm text-gray-600">
          Toutes les routes requièrent le rôle <code className="font-mono text-xs">manage_options</code> + nonce WP REST.
        </p>
        <div className="space-y-1.5">
          {[
            ['GET',    '/wp-json/sj-reviews/v1/dashboard',                    'Stats globales'],
            ['GET',    '/wp-json/sj-reviews/v1/reviews',                      'Liste (filtres: page, per_page, search, rating, source, lieu_id, orderby, order)'],
            ['POST',   '/wp-json/sj-reviews/v1/reviews',                      'Créer'],
            ['PUT',    '/wp-json/sj-reviews/v1/reviews/{id}',                 'Modifier'],
            ['DELETE', '/wp-json/sj-reviews/v1/reviews/{id}',                 'Supprimer'],
            ['GET',    '/wp-json/sj-reviews/v1/lieux',                        'Liste des lieux (avec avis_count)'],
            ['POST',   '/wp-json/sj-reviews/v1/lieux',                        'Créer un lieu'],
            ['PUT',    '/wp-json/sj-reviews/v1/lieux/{id}',                   'Modifier un lieu'],
            ['DELETE', '/wp-json/sj-reviews/v1/lieux/{id}',                   'Supprimer un lieu'],
            ['POST',   '/wp-json/sj-reviews/v1/lieux/{id}/sync-google',       'Sync Google (background, retourne queued)'],
            ['GET',    '/wp-json/sj-reviews/v1/lieux/{id}/sync-status',       'Statut sync (polling)'],
            ['GET',    '/wp-json/sj-reviews/v1/settings',                     'Lire les réglages'],
            ['POST',   '/wp-json/sj-reviews/v1/settings',                     'Enregistrer les réglages'],
            ['POST',   '/wp-json/sj-reviews/v1/settings/test-google-key',     'Tester la clé API Google'],
          ].map(([method, route, desc]) => (
            <div key={route} className="flex items-start gap-2 text-xs">
              <span className={`shrink-0 font-mono font-bold w-14 text-right ${
                method === 'GET' ? 'text-emerald-600' : method === 'POST' ? 'text-blue-600' : method === 'PUT' ? 'text-orange-500' : 'text-red-500'
              }`}>{method}</span>
              <code className="font-mono text-gray-700 shrink-0">{route}</code>
              <span className="text-gray-400">{desc}</span>
            </div>
          ))}
        </div>
      </Section>
    </div>
  )
}
