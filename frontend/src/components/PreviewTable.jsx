import React from 'react';

export default function PreviewTable({ data, onImport, onReset, loading }) {
  if (!data) return null;

  return (
    <div className="glass-card" style={{ padding: 0, overflow: 'hidden' }}>
      {/* Stats */}
      <div style={{ padding: '24px 24px 0' }}>
        <div className="stats-bar">
          <div className="stat-chip">
            <span className="stat-chip__dot stat-chip__dot--total"></span>
            <span className="stat-chip__label">Total Found</span>
            <span className="stat-chip__value">{data.total}</span>
          </div>
          <div className="stat-chip">
            <span className="stat-chip__dot stat-chip__dot--valid"></span>
            <span className="stat-chip__label">Valid</span>
            <span className="stat-chip__value">{data.valid}</span>
          </div>
          <div className="stat-chip">
            <span className="stat-chip__dot stat-chip__dot--invalid"></span>
            <span className="stat-chip__label">Invalid</span>
            <span className="stat-chip__value">{data.invalid}</span>
          </div>
        </div>
      </div>

      {/* Table */}
      <div className="data-table-wrapper" style={{ border: 'none', borderRadius: 0 }}>
        <table className="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Surname</th>
              <th>Email</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {data.results.map((res, i) => (
              <tr key={i}>
                <td className="row-num">{i + 1}</td>
                <td>{res.row[0]}</td>
                <td>{res.row[1]}</td>
                <td style={{ fontFamily: 'monospace', fontSize: '0.8rem' }}>{res.row[2]}</td>
                <td>
                  {res.isValid ? (
                    <span className="status-badge status-badge--valid">
                      <span className="status-badge__dot"></span>
                      Valid
                    </span>
                  ) : (
                    <div className="tooltip-wrapper">
                      <span className="status-badge status-badge--error">
                        <span className="status-badge__dot"></span>
                        Error
                      </span>
                      {res.errors.length > 0 && (
                        <div className="tooltip-content">
                          {res.errors.join(', ')}
                        </div>
                      )}
                    </div>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Actions */}
      <div style={{
        padding: '20px 24px',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        borderTop: '1px solid var(--border-subtle)',
        gap: '12px',
        flexWrap: 'wrap'
      }}>
        <button className="btn btn-secondary" onClick={onReset} disabled={loading}>
          &larr; Back
        </button>
        {data.valid > 0 && (
          <button className="btn btn-primary" onClick={onImport} disabled={loading} id="import-btn">
            {loading ? (
              <>
                <div className="spinner" style={{ width: 16, height: 16, borderWidth: 2 }}></div>
                Importing...
              </>
            ) : (
              <>Import {data.valid} Valid Record{data.valid !== 1 ? 's' : ''}</>
            )}
          </button>
        )}
      </div>
    </div>
  );
}