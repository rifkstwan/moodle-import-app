import React from 'react';

export default function ImportResult({ result, onReset }) {
  if (!result) return null;

  return (
    <div className="glass-card result-card">
      <div className="result-card__icon-wrapper">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#10b981" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
      </div>
      <h2 className="result-card__title">Import Complete</h2>
      <p className="result-card__description">
        Successfully imported <strong>{result.valid}</strong> user{result.valid !== 1 ? 's' : ''} into the database.
      </p>
      {result.invalid > 0 && (
        <p className="result-card__secondary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: '8px', verticalAlign: 'middle' }}>
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
          </svg>
          Skipped {result.invalid} invalid record{result.invalid !== 1 ? 's' : ''}.
        </p>
      )}
      <button className="btn btn-secondary" onClick={onReset} id="upload-another-btn">
        &larr; Upload Another File
      </button>
    </div>
  );
}