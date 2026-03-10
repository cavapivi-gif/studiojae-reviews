export default function MiniBarChart({ data, label = 'avis' }) {
  if (!data?.length) return <p className="text-xs text-gray-400 italic">Pas de données</p>
  const max = Math.max(...data.map(d => d.count), 1)
  return (
    <div className="flex items-end gap-[3px] h-24">
      {data.map((d, i) => (
        <div key={i} className="flex-1 flex flex-col items-center gap-1 min-w-0">
          <span className="text-[9px] text-gray-400 leading-none">{d.count || ''}</span>
          <div
            className="w-full bg-black transition-all duration-300 min-h-[2px]"
            style={{ height: `${Math.max((d.count / max) * 100, 2)}%` }}
            title={`${d.label}: ${d.count} ${label}`}
          />
          <span className="text-[9px] text-gray-400 leading-none truncate w-full text-center">{d.label}</span>
        </div>
      ))}
    </div>
  )
}
