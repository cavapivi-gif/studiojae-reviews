import { IconCheck } from '../../components/Icons'
import { ONBOARDING_STEPS } from './constants'

/* ── Onboarding step indicator ───────────────────────────────────── */
export default function OnboardingNav({ current, onGo, completedSteps }) {
  return (
    <div className="flex items-center gap-0 mb-8">
      {ONBOARDING_STEPS.map((step, i) => {
        const Icon = step.icon
        const done = completedSteps.includes(step.id)
        const active = i === current
        return (
          <div key={step.id} className="flex items-center">
            <button
              type="button"
              onClick={() => onGo(i)}
              className={`flex items-center gap-2 px-3 py-2 text-sm font-medium rounded transition-colors ${
                active
                  ? 'bg-black text-white'
                  : done
                  ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100'
                  : 'text-gray-400 hover:text-gray-600'
              }`}
            >
              <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold ${
                active ? 'bg-white text-black' : done ? 'bg-emerald-200 text-emerald-800' : 'bg-gray-200 text-gray-500'
              }`}>
                {done ? <IconCheck size={12} strokeWidth={3} /> : i + 1}
              </span>
              <span className="hidden sm:inline">{step.label}</span>
            </button>
            {i < ONBOARDING_STEPS.length - 1 && (
              <div className={`w-8 h-px ${i < current ? 'bg-emerald-300' : 'bg-gray-200'}`} />
            )}
          </div>
        )
      })}
    </div>
  )
}
