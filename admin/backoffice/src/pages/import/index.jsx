import { useState } from 'react'
import { PageHeader } from '../../components/ui'
import { ONBOARDING_STEPS } from './constants'
import OnboardingNav from './OnboardingNav'
import StepSources from './StepSources'
import StepLieux from './StepLieux'
import StepImport from './CsvWizard'
import StepDone from './StepDone'

/* ═══════════════════════════════════════════════════════════════════
   MAIN COMPONENT
   ═══════════════════════════════════════════════════════════════════ */
export default function Import() {
  const [step, setStep] = useState(0)
  const [completedSteps, setCompletedSteps] = useState([])

  const markDone = (stepId) => {
    setCompletedSteps(prev => prev.includes(stepId) ? prev : [...prev, stepId])
  }

  const goNext = (currentStepId) => {
    markDone(currentStepId)
    setStep(s => Math.min(s + 1, ONBOARDING_STEPS.length - 1))
  }

  return (
    <div>
      <PageHeader
        title="Onboarding"
        subtitle="Configurez SJ Reviews en quelques étapes"
      />

      <div className="px-8 py-6">
        <OnboardingNav
          current={step}
          onGo={setStep}
          completedSteps={completedSteps}
        />

        {step === 0 && (
          <StepSources
            onNext={() => goNext('sources')}
            onSkip={() => goNext('sources')}
          />
        )}
        {step === 1 && (
          <StepLieux
            onNext={() => goNext('lieux')}
            onBack={() => setStep(0)}
          />
        )}
        {step === 2 && (
          <StepImport
            onNext={() => goNext('import')}
            onBack={() => setStep(1)}
          />
        )}
        {step === 3 && (
          <StepDone />
        )}
      </div>
    </div>
  )
}
