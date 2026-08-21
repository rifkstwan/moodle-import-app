import React, { useState } from 'react';
import FileUpload from './components/FileUpload';
import PreviewTable from './components/PreviewTable';
import ImportResult from './components/ImportResult';
import { parseCsv, importCsv } from './api/importApi';

export default function App() {
  const [file, setFile] = useState(null);
  const [previewData, setPreviewData] = useState(null);
  const [importResult, setImportResult] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleFileSelect = async (selectedFile) => {
    setFile(selectedFile);
    setError('');
    setImportResult(null);
    setLoading(true);
    try {
      const data = await parseCsv(selectedFile);
      setPreviewData(data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleImport = async () => {
    setLoading(true);
    setError('');
    try {
      const result = await importCsv(file);
      setImportResult(result);
      setPreviewData(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleReset = () => {
    setFile(null);
    setPreviewData(null);
    setImportResult(null);
    setError('');
  };

  return (
    <div className="app-container">
      <header className="app-header">
        <div className="app-header__logo">
          <div className="app-header__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
          </div>
          <h1 className="app-header__title">Moodle User Import</h1>
        </div>
        <p className="app-header__subtitle">
          Upload, validate, and import users from CSV into your platform
        </p>
      </header>

      {error && (
        <div className="error-banner">
          <span className="error-banner__icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fca5a5" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
              <line x1="12" y1="9" x2="12" y2="13" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
          </span>
          <span className="error-banner__text">{error}</span>
        </div>
      )}

      {loading && !previewData && !importResult && (
        <div className="glass-card">
          <div className="spinner-wrapper">
            <div className="spinner"></div>
            <span className="spinner-text">Processing your file...</span>
          </div>
        </div>
      )}

      {!loading && !previewData && !importResult && (
        <FileUpload onFileSelect={handleFileSelect} />
      )}

      {previewData && !importResult && (
        <PreviewTable
          data={previewData}
          onImport={handleImport}
          onReset={handleReset}
          loading={loading}
        />
      )}

      {importResult && (
        <ImportResult result={importResult} onReset={handleReset} />
      )}
    </div>
  );
}