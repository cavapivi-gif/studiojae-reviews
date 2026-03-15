import { useState, useEffect, useCallback, useMemo } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../../lib/api'
import { Btn, Input, Select, Notice, Spinner, Toggle } from '../../components/ui'
import { IconUpload, IconChevronRight, IconCheck } from '../../components/Icons'
import { useToast } from '../../components/Toast'
import { SOURCE_LABELS } from '../../lib/constants'
import { CSV_STEPS, SJ_FIELDS } from './constants'
import { parseCSV, detectField, mapRowToSJ } from './csv-utils'

/* ── CSV Step Bar ─────────────────────────────────────────────────── */
function CsvStepBar({ current }) {
  return (
    <div className="flex items-center gap-0 mb-6">
      {CSV_STEPS.map((label, i) => (
        <div key={label} className="flex items-center">
          <div className={`flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded ${
            i === current
              ? 'bg-black text-white'
              : i < current
              ? 'text-gray-500 bg-gray-100'
              : 'text-gray-400'
          }`}>
            <span className={`w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold ${
              i === current ? 'bg-white text-black' : i < current ? 'bg-gray-400 text-white' : 'bg-gray-200 text-gray-500'
            }`}>
              {i < current ? '✓' : i + 1}
            </span>
            {label}
          </div>
          {i < CSV_STEPS.length - 1 && (
            <div className={`w-6 h-px ${i < current ? 'bg-gray-400' : 'bg-gray-200'}`} />
          )}
        </div>
      ))}
    </div>
  )
}

/* ── CSV Step 1 : Fichier ────────────────────────────────────────── */
function CsvStep1File({ onNext, onSkip, onBack }) {
  const [csvText, setCsvText] = useState('')
  const [error, setError] = useState('')
  const [dragging, setDragging] = useState(false)

  const handleFile = (file) => {
    if (!file) return
    const reader = new FileReader()
    reader.onload = (e) => setCsvText(e.target.result)
    reader.readAsText(file, 'utf-8')
  }

  const handleDrop = useCallback((e) => {
    e.preventDefault()
    setDragging(false)
    const file = e.dataTransfer.files[0]
    if (file) handleFile(file)
  }, [])

  const handleAnalyze = () => {
    if (!csvText.trim()) { setError('Collez du CSV ou chargez un fichier.'); return }
    const parsed = parseCSV(csvText)
    if (!parsed || parsed.headers.length < 2) { setError('Format CSV invalide ou trop peu de colonnes.'); return }
    if (parsed.rows.length === 0) { setError('Aucune ligne de données trouvée.'); return }
    setError('')
    onNext({ csvText, ...parsed })
  }

  return (
    <div className="max-w-2xl">
      <CsvStepBar current={0} />

      {/* Drop zone */}
      <div
        className={`border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors mb-4 ${
          dragging ? 'border-black bg-gray-50' : 'border-gray-300 hover:border-gray-400'
        }`}
        onDragOver={(e) => { e.preventDefault(); setDragging(true) }}
        onDragLeave={() => setDragging(false)}
        onDrop={handleDrop}
        onClick={() => document.getElementById('csv-file-input').click()}
      >
        <IconUpload size={24} className="mx-auto text-gray-400 mb-2" />
        <p className="text-sm text-gray-600">Glissez un fichier CSV ici, ou <span className="underline">cliquez pour choisir</span></p>
        <p className="text-xs text-gray-400 mt-1">Format : UTF-8, séparateur virgule ou point-virgule</p>
        <input
          id="csv-file-input"
          type="file"
          accept=".csv,.txt"
          className="hidden"
          onChange={(e) => handleFile(e.target.files[0])}
        />
      </div>

      <div className="relative mb-4">
        <div className="absolute inset-0 flex items-center"><div className="w-full border-t border-gray-200" /></div>
        <div className="relative flex justify-center text-xs text-gray-400"><span className="bg-white px-3">ou collez le contenu CSV</span></div>
      </div>

      <textarea
        className="w-full border border-gray-200 rounded p-3 text-xs font-mono h-40 resize-y focus:outline-none focus:border-gray-400"
        placeholder="Produit;N° commande;Date réservation;..."
        value={csvText}
        onChange={(e) => setCsvText(e.target.value)}
      />

      {error && <Notice type="error" className="mt-3">{error}</Notice>}
      {csvText && (
        <p className="text-xs text-gray-400 mt-2">
          {csvText.trim().split('\n').length - 1} ligne(s) détectée(s)
        </p>
      )}

      <div className="flex justify-between items-center mt-6">
        <div className="flex items-center gap-3">
          <Btn variant="secondary" onClick={onBack}>Retour</Btn>
          <button type="button" onClick={onSkip} className="text-sm text-gray-400 hover:text-gray-600">
            Passer l'import
          </button>
        </div>
        <Btn onClick={handleAnalyze} disabled={!csvText.trim()}>Analyser</Btn>
      </div>
    </div>
  )
}

/* ── CSV Step 2 : Colonnes ───────────────────────────────────────── */
function CsvStep2Columns({ data, onNext, onBack }) {
  const [columnMap, setColumnMap] = useState(() => {
    const map = {}
    data.headers.forEach((h, i) => {
      const samples = data.rows.slice(0, 5).map(row => row[i] ?? '')
      map[i] = detectField(h, samples)
    })
    return map
  })

  const preview = data.rows.slice(0, 3)

  return (
    <div>
      <p className="text-sm text-gray-500 mb-4">
        {data.rows.length} ligne(s) importées. Assignez chaque colonne CSV à un champ SJ Reviews.
      </p>

      <div className="border border-gray-200 overflow-auto">
        <table className="min-w-full text-sm">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 w-8">#</th>
              <th className="px-3 py-2 text-left text-xs font-medium text-gray-500">Colonne CSV</th>
              <th className="px-3 py-2 text-left text-xs font-medium text-gray-500">Champ SJ</th>
              {preview.map((_, ri) => (
                <th key={ri} className="px-3 py-2 text-left text-xs font-medium text-gray-400">Ex. {ri + 1}</th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {data.headers.map((header, i) => (
              <tr key={i} className={columnMap[i] === '_ignore' ? 'opacity-40' : ''}>
                <td className="px-3 py-2 text-xs text-gray-400">{i + 1}</td>
                <td className="px-3 py-2 font-medium text-gray-700">{header}</td>
                <td className="px-3 py-2">
                  <select
                    className="text-xs border border-gray-200 rounded px-2 py-1 w-full"
                    value={columnMap[i] ?? '_ignore'}
                    onChange={(e) => setColumnMap(prev => ({ ...prev, [i]: e.target.value }))}
                  >
                    {SJ_FIELDS.map(f => (
                      <option key={f.value} value={f.value}>{f.label}</option>
                    ))}
                  </select>
                </td>
                {preview.map((row, ri) => (
                  <td key={ri} className="px-3 py-2 text-xs text-gray-500 max-w-32 truncate">{row[i] ?? ''}</td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="flex justify-between mt-6">
        <Btn variant="secondary" onClick={onBack}>Retour</Btn>
        <Btn onClick={() => onNext({ columnMap })}>Défauts <IconChevronRight size={14} /></Btn>
      </div>
    </div>
  )
}

/* ── CSV Step 3 : Défauts ────────────────────────────────────────── */
function CsvStep3Defaults({ onNext, onBack }) {
  const [lieux, setLieux] = useState([])
  const [defaults, setDefaults] = useState({
    lieu_id: '', source: 'regiondo', certified: true, language: 'fr', sub_criteria_auto: true,
  })

  useEffect(() => {
    api.lieux().then(data => {
      setLieux(data)
      const first = data.find(l => l.active) ?? data[0]
      if (first) setDefaults(prev => ({ ...prev, lieu_id: first.id }))
    }).catch(() => {})
  }, [])

  const set = (key, val) => setDefaults(prev => ({ ...prev, [key]: val }))

  return (
    <div className="max-w-lg">
      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Lieu rattaché</label>
          <select className="w-full border border-gray-200 rounded px-3 py-2 text-sm" value={defaults.lieu_id} onChange={(e) => set('lieu_id', e.target.value)}>
            <option value="">— Aucun lieu —</option>
            {lieux.map(l => <option key={l.id} value={l.id}>{l.name}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Source</label>
          <select className="w-full border border-gray-200 rounded px-3 py-2 text-sm" value={defaults.source} onChange={(e) => set('source', e.target.value)}>
            {Object.entries(SOURCE_LABELS).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Langue par défaut</label>
          <select className="w-full border border-gray-200 rounded px-3 py-2 text-sm" value={defaults.language} onChange={(e) => set('language', e.target.value)}>
            <option value="fr">Français</option>
            <option value="en">Anglais</option>
            <option value="it">Italien</option>
            <option value="de">Allemand</option>
            <option value="es">Espagnol</option>
          </select>
        </div>
        <div className="flex items-center justify-between py-2 border-t border-gray-100">
          <div>
            <p className="text-sm font-medium text-gray-700">Avis certifié</p>
            <p className="text-xs text-gray-500">Marquer tous les avis importés comme certifiés</p>
          </div>
          <Toggle checked={defaults.certified} onChange={(v) => set('certified', v)} />
        </div>
        <div className="flex items-center justify-between py-2 border-t border-gray-100">
          <div>
            <p className="text-sm font-medium text-gray-700">Sous-critères automatiques</p>
            <p className="text-xs text-gray-500">Hérite la note globale pour qualité/prix, ambiance, expérience, paysage</p>
          </div>
          <Toggle checked={defaults.sub_criteria_auto} onChange={(v) => set('sub_criteria_auto', v)} />
        </div>
      </div>
      <div className="flex justify-between mt-6">
        <Btn variant="secondary" onClick={onBack}>Retour</Btn>
        <Btn onClick={() => onNext({ defaults })}>Produits <IconChevronRight size={14} /></Btn>
      </div>
    </div>
  )
}

/* ── CSV Step 4 : Produits ───────────────────────────────────────── */
function CsvStep4Products({ data, columnMap, onNext, onBack }) {
  const [posts, setPosts] = useState([])
  const [loadingPosts, setLoadingPosts] = useState(true)
  const [productMap, setProductMap] = useState({})

  const productColIdx = Object.entries(columnMap).filter(([, v]) => v === 'product').at(-1)?.[0]
  const uniqueProducts = useMemo(() => {
    if (productColIdx === undefined) return []
    const seen = new Set()
    data.rows.forEach(row => {
      const val = (row[productColIdx] ?? '').trim()
      if (val) seen.add(val)
    })
    return [...seen]
  }, [data.rows, productColIdx])

  useEffect(() => {
    api.importPostMatches()
      .then(setPosts)
      .catch(() => setPosts([]))
      .finally(() => setLoadingPosts(false))
  }, [])

  if (!uniqueProducts.length) {
    return (
      <div className="max-w-lg">
        <Notice type="info">Aucune colonne "Produit" mappée — étape ignorée.</Notice>
        <div className="flex justify-between mt-6">
          <Btn variant="secondary" onClick={onBack}>Retour</Btn>
          <Btn onClick={() => onNext({ productMap: {} })}>Aperçu <IconChevronRight size={14} /></Btn>
        </div>
      </div>
    )
  }

  return (
    <div>
      <p className="text-sm text-gray-500 mb-4">Associez chaque produit à un post WordPress.</p>
      {loadingPosts ? (
        <div className="flex items-center gap-2 py-4"><Spinner size={16} /><span className="text-sm text-gray-500">Chargement des posts...</span></div>
      ) : (
        <div className="border border-gray-200 divide-y">
          {uniqueProducts.map(product => (
            <div key={product} className="flex items-center gap-4 px-4 py-3">
              <div className="flex-1 text-sm font-medium text-gray-700 min-w-0 truncate">{product}</div>
              <div className="w-px h-6 bg-gray-200" />
              <select
                className="flex-1 border border-gray-200 rounded px-3 py-1.5 text-sm"
                value={productMap[product] ?? ''}
                onChange={(e) => setProductMap(prev => ({ ...prev, [product]: e.target.value ? parseInt(e.target.value) : 0 }))}
              >
                <option value="">— Ignorer —</option>
                {posts.map(p => <option key={p.id} value={p.id}>{p.title}</option>)}
              </select>
            </div>
          ))}
        </div>
      )}
      <div className="flex justify-between mt-6">
        <Btn variant="secondary" onClick={onBack}>Retour</Btn>
        <Btn onClick={() => onNext({ productMap })}>Aperçu <IconChevronRight size={14} /></Btn>
      </div>
    </div>
  )
}

/* ── CSV Step 5 : Aperçu & Import ────────────────────────────────── */
function CsvStep5Preview({ data, columnMap, defaults, productMap, onBack, onDone }) {
  const [preview, setPreview]   = useState(null)
  const [loading, setLoading]   = useState(false)
  const [importing, setImporting] = useState(false)
  const [result, setResult]     = useState(null)
  const [error, setError]       = useState('')

  const mappedRows = useMemo(() => data.rows.map(row => mapRowToSJ(row, data.headers, columnMap)), [data, columnMap])

  const runPreview = async () => {
    setLoading(true); setError('')
    try {
      setPreview(await api.importPreview({ rows: mappedRows, defaults, product_map: productMap }))
    } catch (e) { setError(e.message) }
    finally { setLoading(false) }
  }

  const runImport = async () => {
    setImporting(true); setError('')
    try {
      setResult(await api.importExecute({ rows: mappedRows, defaults, product_map: productMap }))
    } catch (e) { setError(e.message) }
    finally { setImporting(false) }
  }

  const statusClass = {
    new:       'text-green-700 bg-green-50',
    duplicate: 'text-yellow-700 bg-yellow-50',
    error:     'text-red-700 bg-red-50',
  }
  const statusLabel = { new: 'Nouveau', duplicate: 'Doublon', error: 'Erreur' }

  if (result) {
    return (
      <div className="max-w-lg text-center py-8">
        <div className="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
          <IconCheck size={24} strokeWidth={2.5} className="text-emerald-600" />
        </div>
        <h2 className="text-lg font-bold mb-2">Import terminé</h2>
        <div className="text-sm text-gray-600 space-y-1">
          <p><span className="font-semibold text-green-700">{result.imported}</span> avis importés</p>
          <p><span className="font-semibold text-yellow-600">{result.skipped}</span> doublons ignorés</p>
          {result.errors?.length > 0 && (
            <p><span className="font-semibold text-red-600">{result.errors.length}</span> erreurs</p>
          )}
        </div>
        {result.errors?.length > 0 && (
          <div className="mt-4 text-left border border-red-200 rounded p-3 bg-red-50 text-xs text-red-700 space-y-1">
            {result.errors.slice(0, 10).map((e, i) => <p key={i}>{e}</p>)}
          </div>
        )}
        <Btn className="mt-6" onClick={onDone}>Terminer l'onboarding <IconChevronRight size={14} /></Btn>
      </div>
    )
  }

  return (
    <div>
      <p className="text-sm text-gray-500 mb-4">
        {mappedRows.length} ligne(s) à traiter.{' '}
        {preview && (
          <span>
            <span className="text-green-700 font-medium">{preview.counts.new} nouveaux</span>
            {preview.counts.duplicate > 0 && <span> · <span className="text-yellow-600 font-medium">{preview.counts.duplicate} doublons</span></span>}
            {preview.counts.error > 0 && <span> · <span className="text-red-600 font-medium">{preview.counts.error} erreurs</span></span>}
          </span>
        )}
      </p>

      {error && <Notice type="error" className="mb-4">{error}</Notice>}

      {!preview ? (
        <div className="flex justify-between mt-6">
          <Btn variant="secondary" onClick={onBack}>Retour</Btn>
          <Btn onClick={runPreview} loading={loading}>Analyser l'aperçu</Btn>
        </div>
      ) : (
        <>
          <div className="border border-gray-200 overflow-auto max-h-96">
            <table className="min-w-full text-xs">
              <thead className="bg-gray-50 sticky top-0">
                <tr>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Statut</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Auteur</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Note</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Produit</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">N° commande</th>
                  <th className="px-3 py-2 text-left font-medium text-gray-500">Raison</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {preview.rows.map((item, i) => (
                  <tr key={i} className={item.status === 'error' ? 'bg-red-50' : item.status === 'duplicate' ? 'bg-yellow-50' : ''}>
                    <td className="px-3 py-2">
                      <span className={`inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium ${statusClass[item.status]}`}>
                        {statusLabel[item.status]}
                      </span>
                    </td>
                    <td className="px-3 py-2 font-medium">{item.row.author || '—'}</td>
                    <td className="px-3 py-2">{item.row.rating || '—'}</td>
                    <td className="px-3 py-2 max-w-32 truncate">{item.row.product || '—'}</td>
                    <td className="px-3 py-2">{item.row.order_id || '—'}</td>
                    <td className="px-3 py-2 text-gray-500">{item.reason || ''}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div className="flex justify-between items-center mt-6">
            <Btn variant="secondary" onClick={onBack}>Retour</Btn>
            <div className="flex items-center gap-3">
              <Btn variant="ghost" size="sm" onClick={runPreview} loading={loading}>Actualiser</Btn>
              <Btn onClick={runImport} disabled={importing || preview.counts.new === 0} loading={importing}>
                Importer {preview.counts.new} avis
              </Btn>
            </div>
          </div>
        </>
      )}
    </div>
  )
}

/* ═══════════════════════════════════════════════════════════════════
   STEP 3 : Import CSV — full wizard embedded inline
   ═══════════════════════════════════════════════════════════════════ */
export default function StepImport({ onNext, onBack }) {
  const navigate = useNavigate()
  const [csvStep, setCsvStep] = useState(0)
  const [csvState, setCsvState] = useState({})

  const csvNext = (data) => {
    setCsvState(prev => ({ ...prev, ...data }))
    setCsvStep(s => s + 1)
  }
  const csvBack = () => setCsvStep(s => Math.max(0, s - 1))

  return (
    <div>
      <div className="mb-6">
        <h2 className="text-base font-semibold mb-1">Importer des avis CSV</h2>
        <p className="text-sm text-gray-500">
          Si vous avez des avis dans un fichier CSV (export Regiondo, tableur...), importez-les ici.
          Les avis Google, Trustpilot et TripAdvisor se synchronisent automatiquement via l'étape précédente.
        </p>
      </div>

      {csvStep === 0 && (
        <CsvStep1File
          onNext={csvNext}
          onSkip={onNext}
          onBack={onBack}
        />
      )}
      {csvStep === 1 && csvState.headers && (
        <>
          <CsvStepBar current={1} />
          <CsvStep2Columns data={csvState} onNext={csvNext} onBack={csvBack} />
        </>
      )}
      {csvStep === 2 && (
        <>
          <CsvStepBar current={2} />
          <CsvStep3Defaults onNext={csvNext} onBack={csvBack} />
        </>
      )}
      {csvStep === 3 && (
        <>
          <CsvStepBar current={3} />
          <CsvStep4Products data={csvState} columnMap={csvState.columnMap ?? {}} onNext={csvNext} onBack={csvBack} />
        </>
      )}
      {csvStep === 4 && (
        <>
          <CsvStepBar current={4} />
          <CsvStep5Preview
            data={csvState}
            columnMap={csvState.columnMap ?? {}}
            defaults={csvState.defaults ?? {}}
            productMap={csvState.productMap ?? {}}
            onBack={csvBack}
            onDone={onNext}
          />
        </>
      )}
    </div>
  )
}
