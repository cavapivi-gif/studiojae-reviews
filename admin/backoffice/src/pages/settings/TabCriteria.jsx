import { Input } from '../../components/ui'
import { SectionHeader } from './shared'

/**
 * Onglet Critères — labels des sous-critères et niveaux de note.
 *
 * @param {{
 *   form: object,
 *   setCriteriaLabel: (key: string) => (e: Event) => void,
 *   setRatingLabel: (key: string) => (e: Event) => void,
 *   DEFAULTS: object,
 * }} props
 */
export default function TabCriteria({ form, setCriteriaLabel, setRatingLabel, DEFAULTS }) {
  return (
    <div className="flex flex-col gap-7">
      <section>
        <SectionHeader>Labels des sous-critères</SectionHeader>
        <p className="text-xs text-gray-400 mb-4">
          Personnalisez les noms des sous-critères affichés dans les widgets et shortcodes.
        </p>
        <div className="grid grid-cols-2 gap-4">
          {Object.entries(form.criteria_labels).map(([key, label]) => (
            <Input
              key={key}
              label={<span className="font-mono text-gray-400">{key}</span>}
              value={label}
              onChange={setCriteriaLabel(key)}
              placeholder={DEFAULTS.criteria_labels[key]}
            />
          ))}
        </div>
      </section>

      <section>
        <SectionHeader>Labels des niveaux de note</SectionHeader>
        <p className="text-xs text-gray-400 mb-4">
          Personnalisez les noms affichés dans la répartition par étoiles (ex. « Excellent », « Bien »…).
        </p>
        <div className="grid grid-cols-2 gap-4">
          {['5', '4', '3', '2', '1'].map(star => (
            <Input
              key={star}
              label={<span className="text-gray-400">{'★'.repeat(Number(star))} ({star})</span>}
              value={form.rating_labels[star] || ''}
              onChange={setRatingLabel(star)}
              placeholder={DEFAULTS.rating_labels[star]}
            />
          ))}
        </div>
      </section>
    </div>
  )
}
