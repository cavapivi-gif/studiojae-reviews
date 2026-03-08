import { Component } from 'react'

export default class ErrorBoundary extends Component {
  constructor(props) {
    super(props)
    this.state = { error: null }
  }

  static getDerivedStateFromError(error) {
    return { error }
  }

  render() {
    if (this.state.error) {
      return (
        <div className="flex items-center justify-center min-h-screen bg-white">
          <div className="max-w-md w-full mx-4 border border-red-200 p-8">
            <h2 className="text-sm font-semibold uppercase tracking-widest text-red-600 mb-3">
              Erreur inattendue
            </h2>
            <p className="text-sm text-gray-600 mb-4">
              Un problème est survenu dans l'interface. Rechargez la page pour réessayer.
            </p>
            <pre className="text-xs text-red-500 bg-red-50 border border-red-100 p-3 overflow-auto max-h-40 mb-4">
              {this.state.error?.message}
            </pre>
            <button
              onClick={() => window.location.reload()}
              className="px-4 py-2 bg-black text-white text-sm hover:bg-gray-800 transition-colors"
            >
              Recharger
            </button>
          </div>
        </div>
      )
    }
    return this.props.children
  }
}
