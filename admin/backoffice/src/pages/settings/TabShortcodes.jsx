import { SectionHeader } from './shared'

/**
 * Onglet Shortcodes — référence de tous les shortcodes disponibles du plugin.
 */
export default function TabShortcodes() {
  return (
    <div className="flex flex-col gap-7">
      <section>
        <SectionHeader>Shortcodes disponibles</SectionHeader>
        <div className="bg-gray-50 border border-gray-200 px-4 py-3 text-xs font-mono text-gray-600 space-y-1">
          <div className="text-gray-400 mb-2">{/* Avis (liste/slider) */}</div>
          <div>[sj_reviews layout="slider-i" preset="minimal" max="5"]</div>
          <div>[sj_reviews layout="grid" preset="white" columns="3"]</div>
          <div>[sj_reviews lieu_id="lieu_xxxxxxxx"]</div>
          <div className="text-gray-400 mt-3 mb-1">{/* Résumé statistique */}</div>
          <div>[sj_summary]</div>
          <div>[sj_summary lieu_id="lieu_xxxxxxxx" show_distribution="1" show_criteria="1"]</div>
          <div className="text-gray-400 mt-3 mb-1">{/* Badge de note */}</div>
          <div>[sj_rating design="card" lieu_id="all"]</div>
          <div className="text-gray-400 mt-3 mb-1">{/* Note inline */}</div>
          <div>[sj_inline_rating show_stars="1" show_score="1"]</div>
        </div>
      </section>
    </div>
  )
}
