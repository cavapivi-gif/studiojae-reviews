import { IconCheck } from '../../components/Icons'
import { SectionHeader, Tutorial } from './shared'

/**
 * Onglet Liaisons — liaison avis <-> post types WordPress.
 *
 * @param {{
 *   form: object,
 *   setForm: (fn: (f: object) => object) => void,
 *   availablePostTypes: Array<{ slug: string, label: string }>,
 * }} props
 */
export default function TabLinks({ form, setForm, availablePostTypes }) {
  return (
    <div className="flex flex-col gap-7">
      <section>
        <SectionHeader>Liaison avis ↔ Post</SectionHeader>
        <p className="text-xs text-gray-500 mb-3">
          Sélectionnez les types de contenu auxquels un avis peut être lié.
          Une meta box <strong>« Lieu SJ Reviews »</strong> apparaîtra sur ces contenus.
        </p>
        {availablePostTypes.length === 0 ? (
          <p className="text-xs text-gray-400 italic">Aucun post type public trouvé.</p>
        ) : (
          <div className="flex flex-col gap-2">
            {availablePostTypes.map(pt => {
              const checked = form.linked_post_types.includes(pt.slug)
              return (
                <label
                  key={pt.slug}
                  className={`flex items-center gap-3 cursor-pointer px-3 py-2 border transition-all duration-150
                    ${checked ? 'border-indigo-200 bg-indigo-50/50' : 'border-gray-100 bg-gray-50/50 hover:border-gray-200'}`}
                >
                  <div className={`w-4 h-4 border-2 flex items-center justify-center transition-all
                    ${checked ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 bg-white'}`}>
                    {checked && <IconCheck size={10} strokeWidth={3} className="text-white" />}
                  </div>
                  <input type="checkbox" checked={checked} onChange={() => {
                    const next = checked
                      ? form.linked_post_types.filter(s => s !== pt.slug)
                      : [...form.linked_post_types, pt.slug]
                    setForm(f => ({ ...f, linked_post_types: next }))
                  }} className="sr-only" />
                  <span className="text-sm font-medium text-gray-700">{pt.label}</span>
                  <code className="text-xs text-gray-400 font-mono ml-auto">{pt.slug}</code>
                </label>
              )
            })}
          </div>
        )}
        <Tutorial title="Comment fonctionne la liaison bidirectionnelle ?">
          <p><strong>Depuis l'avis :</strong> choisissez le <em>Post lié</em> dans le formulaire → l'avis est associé à ce post.</p>
          <p><strong>Depuis le post :</strong> la meta box <em>Lieu SJ Reviews</em> (dans la sidebar d'édition) vous permet de sélectionner le lieu correspondant.</p>
          <p><strong>Widget Résumé Avis :</strong> en mode <em>Auto</em>, il lit le lieu du post et filtre les statistiques automatiquement.</p>
        </Tutorial>
      </section>
    </div>
  )
}
