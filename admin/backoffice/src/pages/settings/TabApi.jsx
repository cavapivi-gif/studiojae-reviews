import { api } from '../../lib/api'
import { Input, Select, Btn, Toggle } from '../../components/ui'
import { useToast } from '../../components/Toast'
import { SectionHeader, ApiKeyField, Tutorial } from './shared'

/**
 * Onglet API & Sync — clés, cache, fréquence, digest email, social proof toast.
 *
 * @param {{
 *   form: object,
 *   set: (key: string) => (value: any) => void,
 *   googleKeyStatus: string|null,
 *   googleKeyMsg: string,
 *   trustpilotKeyStatus: string|null,
 *   trustpilotKeyMsg: string,
 *   tripadvisorKeyStatus: string|null,
 *   tripadvisorKeyMsg: string,
 *   anthropicKeyStatus: string|null,
 *   anthropicKeyMsg: string,
 *   aiSummaryLoading: boolean,
 *   digestTestLoading: boolean,
 *   handleSave: () => Promise<void>,
 *   testGoogleKey: () => Promise<void>,
 *   testTrustpilotKey: () => Promise<void>,
 *   testTripadvisorKey: () => Promise<void>,
 *   testAnthropicKey: () => Promise<void>,
 *   setAiSummaryLoading: (v: boolean) => void,
 *   setDigestTestLoading: (v: boolean) => void,
 * }} props
 */
export default function TabApi({
  form, set,
  googleKeyStatus, googleKeyMsg,
  trustpilotKeyStatus, trustpilotKeyMsg,
  tripadvisorKeyStatus, tripadvisorKeyMsg,
  anthropicKeyStatus, anthropicKeyMsg,
  aiSummaryLoading, digestTestLoading,
  handleSave,
  testGoogleKey, testTrustpilotKey, testTripadvisorKey, testAnthropicKey,
  setAiSummaryLoading, setDigestTestLoading,
}) {
  const toast = useToast()

  return (
    <div className="flex flex-col gap-7">
      <section>
        <SectionHeader badge="Google Places">Google Maps API</SectionHeader>
        <ApiKeyField
          label="Clé API Google Maps"
          value={form.google_api_key}
          onChange={set('google_api_key')}
          onTest={testGoogleKey}
          testStatus={googleKeyStatus}
          testMsg={googleKeyMsg}
          tutorial={
            <Tutorial title="Comment configurer la clé API Google ?">
              <p><strong>1.</strong> Allez sur <strong>console.cloud.google.com</strong> → Bibliothèque → activez <strong>Places API</strong>.</p>
              <p><strong>2.</strong> Identifiants → Créer des identifiants → Clé API.</p>
              <p><strong>3.</strong> Restrictions recommandées pour cette clé (serveur) :</p>
              <ul className="ml-3 space-y-0.5 list-disc">
                <li><strong>Aucune restriction HTTP referrer</strong> — l'import s'effectue depuis votre serveur.</li>
              </ul>
            </Tutorial>
          }
        />
      </section>

      <section>
        <SectionHeader badge="Trustpilot">Trustpilot Business API</SectionHeader>
        <ApiKeyField
          label="Clé API Trustpilot"
          value={form.trustpilot_api_key}
          onChange={set('trustpilot_api_key')}
          onTest={testTrustpilotKey}
          testStatus={trustpilotKeyStatus}
          testMsg={trustpilotKeyMsg}
          tutorial={
            <Tutorial title="Comment obtenir une clé API Trustpilot ?">
              <p><strong>1.</strong> Créez un compte sur <strong>Trustpilot Business</strong>.</p>
              <p><strong>2.</strong> Allez dans <strong>Integrations → API</strong>.</p>
              <p><strong>3.</strong> Copiez votre <em>API Key</em> (pas le secret).</p>
              <p className="text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1">
                Le domaine Trustpilot se configure par lieu dans <strong>Lieux &amp; Sources</strong>.
              </p>
            </Tutorial>
          }
        />
      </section>

      <section>
        <SectionHeader badge="TripAdvisor">TripAdvisor Content API</SectionHeader>
        <ApiKeyField
          label="Clé API TripAdvisor"
          value={form.tripadvisor_api_key}
          onChange={set('tripadvisor_api_key')}
          onTest={testTripadvisorKey}
          testStatus={tripadvisorKeyStatus}
          testMsg={tripadvisorKeyMsg}
          tutorial={
            <Tutorial title="Comment obtenir une clé API TripAdvisor ?">
              <p><strong>1.</strong> Inscrivez-vous sur <strong>tripadvisor.com/developers</strong>.</p>
              <p><strong>2.</strong> Créez une application et obtenez votre clé API.</p>
              <p>Le <em>Location ID</em> se configure par lieu dans <strong>Lieux &amp; Sources</strong>.</p>
            </Tutorial>
          }
        />
      </section>

      <section>
        <SectionHeader badge="Claude">Anthropic API (Résumé IA)</SectionHeader>
        <div className="flex flex-col gap-3">
          <Input label={<>Clé API Anthropic{form.anthropic_api_key?.trim() ? <span className="inline-block w-2 h-2 rounded-full bg-blue-500 ml-1.5" title="Connecté" /> : null}</>} type="password" value={form.anthropic_api_key} onChange={e => set('anthropic_api_key')(e.target.value)} placeholder="sk-ant-…" autoComplete="off" />
          <Btn type="button" variant="secondary" size="sm" loading={aiSummaryLoading} disabled={!form.anthropic_api_key.trim() || aiSummaryLoading} onClick={async () => {
            setAiSummaryLoading(true)
            try {
              await handleSave()
              const res = await api.aiGenerateSummary('all')
              if (res.ok) toast.success(`Résumé généré (${res.data.review_count} avis analysés).`)
              else toast.error(res.message || 'Erreur de génération.')
            } catch (e) { toast.error(e.message) }
            finally { setAiSummaryLoading(false) }
          }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 2a4 4 0 014 4v1a2 2 0 012 2v1a2 2 0 01-2 2H8a2 2 0 01-2-2V9a2 2 0 012-2V6a4 4 0 014-4z"/><path d="M8 14v4a4 4 0 008 0v-4"/></svg>
            Générer le résumé IA
          </Btn>
          <p className="text-xs text-gray-400">
            Génère un résumé automatique des avis via Claude. Le résumé est mis en cache 24h par lieu.
          </p>
        </div>
      </section>

      <section>
        <SectionHeader>Cache</SectionHeader>
        <p className="text-xs text-gray-500 mb-3">
          Vide le cache du dashboard (stats, tendances, répartitions). Utile après un import ou une sync manuelle.
        </p>
        <Btn type="button" variant="secondary" size="sm" onClick={async () => {
          try {
            await api.flushCache()
            toast.success('Cache vidé avec succès.')
          } catch (e) {
            toast.error(e.message)
          }
        }}>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
          Vider le cache
        </Btn>
      </section>

      <section>
        <SectionHeader>Synchronisation automatique</SectionHeader>
        <Select
          label="Fréquence de sync automatique"
          value={form.sync_frequency}
          onChange={e => set('sync_frequency')(e.target.value)}
        >
          <option value="off">Désactivée</option>
          <option value="twice_daily">2× par jour</option>
          <option value="daily">1× par jour</option>
          <option value="weekly">1× par semaine</option>
          <option value="monthly">1× par mois</option>
        </Select>
        <p className="text-xs text-gray-400 mt-2">
          Synchronise automatiquement les notes et nombres d'avis de tous les lieux actifs (Google, Trustpilot, TripAdvisor).
        </p>
        {form.last_sync && (
          <p className="text-xs text-gray-500 mt-1">
            Dernière sync : <strong>{form.last_sync}</strong>
          </p>
        )}
      </section>

      <section>
        <SectionHeader>Email digest hebdomadaire</SectionHeader>
        <div className="flex flex-col gap-3">
          <Toggle label="Activer le digest hebdomadaire" checked={form.email_digest_enabled === '1'} onChange={e => set('email_digest_enabled')(e.target.checked ? '1' : '0')} />
          <Input label="Email(s) destinataire(s)" value={form.email_digest_email} onChange={e => set('email_digest_email')(e.target.value)} placeholder="admin@example.com" />
          <p className="text-xs text-gray-400">Séparés par des virgules. Vide = email admin du site.</p>
          <Btn type="button" variant="ghost" size="sm" loading={digestTestLoading} onClick={async () => {
            setDigestTestLoading(true)
            try {
              await handleSave()
              await (await fetch(`${window.sjReviews?.rest_url ?? '/wp-json/sj-reviews/v1'}email-digest/test`, {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.sjReviews?.nonce ?? '' }
              })).json()
              toast.success('Email de test envoyé.')
            } catch (e) { toast.error(e.message) }
            finally { setDigestTestLoading(false) }
          }}>
            Envoyer un email de test
          </Btn>
        </div>
      </section>

      <section>
        <SectionHeader>Social proof (toast)</SectionHeader>
        <div className="flex flex-col gap-3">
          <Toggle label="Activer le toast social proof" checked={form.toast_enabled === '1'} onChange={e => set('toast_enabled')(e.target.checked ? '1' : '0')} />
          <div className="grid grid-cols-2 gap-4">
            <Select label="Position" value={form.toast_position} onChange={e => set('toast_position')(e.target.value)}>
              <option value="bottom-left">Bas gauche</option>
              <option value="bottom-right">Bas droite</option>
              <option value="top-left">Haut gauche</option>
              <option value="top-right">Haut droite</option>
            </Select>
            <Input label="Délai d'apparition (ms)" type="number" min="1000" max="30000" step="1000" value={form.toast_delay} onChange={e => set('toast_delay')(e.target.value)} />
          </div>
          <Input label="URL page avis (clic sur toast)" value={form.toast_reviews_url} onChange={e => set('toast_reviews_url')(e.target.value)} placeholder="https://monsite.fr/avis" />
          <p className="text-xs text-gray-400">
            Affiche un toast discret quand un avis récent (&lt;48h) existe. 1 fois par session, auto-masqué après 8s.
          </p>
        </div>
      </section>
    </div>
  )
}
