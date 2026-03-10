import { Spinner } from '../ui'

export default function ChartCard({ title, loading, children, actions }) {
  return (
    <div className="border border-gray-200 p-5">
      <div className="flex items-center justify-between mb-4">
        <span className="text-xs text-gray-400 uppercase tracking-widest">{title}</span>
        {actions}
      </div>
      {loading ? (
        <div className="flex items-center justify-center py-8"><Spinner size={16} /></div>
      ) : (
        children
      )}
    </div>
  )
}
