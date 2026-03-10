import { Spinner } from '../ui'

export default function ChartCard({ title, loading, children, actions }) {
  return (
    <div className="rounded-lg border bg-card p-5">
      <div className="flex items-center justify-between mb-4">
        <span className="text-xs text-muted-foreground uppercase tracking-wider font-medium">{title}</span>
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
