import { Select, Input } from '../../components/ui'
import { SectionHeader } from './shared'

/**
 * Onglet Affichage — layout par défaut, preset de style, couleurs, autoplay.
 *
 * @param {{
 *   form: object,
 *   set: (key: string) => (value: any) => void,
 * }} props
 */
export default function TabDisplay({ form, set }) {
  return (
    <div className="flex flex-col gap-7">
      <section>
        <SectionHeader>Layout par défaut</SectionHeader>
        <div className="grid grid-cols-2 gap-4">
          <Select label="Layout" value={form.default_layout} onChange={e => set('default_layout')(e.target.value)}>
            <option value="slider-i">Slider I</option>
            <option value="slider-ii">Slider II (33/66)</option>
            <option value="badge">Badge</option>
            <option value="grid">Grille</option>
            <option value="list">Liste</option>
          </Select>
          <Select label="Preset de style" value={form.default_preset} onChange={e => set('default_preset')(e.target.value)}>
            <option value="minimal">Minimal</option>
            <option value="dark">Dark</option>
            <option value="white">White</option>
          </Select>
          <Input label="Nombre max d'avis (front)" type="number" min="1" max="20" value={form.max_front} onChange={e => set('max_front')(e.target.value)} />
          <Input label="Mots avant troncature" type="number" min="10" max="200" value={form.text_words} onChange={e => set('text_words')(e.target.value)} />
        </div>
      </section>

      <section>
        <SectionHeader>Apparence</SectionHeader>
        <div className="grid grid-cols-2 gap-4">
          <label className="flex flex-col gap-1">
            <span className="text-xs text-gray-500">Couleur des étoiles</span>
            <div className="flex items-center gap-2">
              <input type="color" value={form.star_color} onChange={e => set('star_color')(e.target.value)} className="w-10 h-9 border border-gray-200 cursor-pointer p-0.5" />
              <code className="text-xs text-gray-400">{form.star_color}</code>
            </div>
          </label>
          <label className="flex flex-col gap-1">
            <span className="text-xs text-gray-500">Couleur des bulles (rating)</span>
            <div className="flex items-center gap-2">
              <input type="color" value={form.bubble_color} onChange={e => set('bubble_color')(e.target.value)} className="w-10 h-9 border border-gray-200 cursor-pointer p-0.5" />
              <code className="text-xs text-gray-400">{form.bubble_color}</code>
            </div>
          </label>
          <Input label="Label badge certifié" value={form.certified_label} onChange={e => set('certified_label')(e.target.value)} placeholder="Certifié" />
          <Input label="Délai autoplay slider (ms)" type="number" min="1000" max="15000" step="500" value={form.autoplay_delay} onChange={e => set('autoplay_delay')(e.target.value)} />
        </div>
      </section>
    </div>
  )
}
