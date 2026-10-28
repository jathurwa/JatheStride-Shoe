import React, { useState, useMemo } from 'react';
import JSZip from 'jszip';
import { 
  FileCode, 
  Download, 
  Copy, 
  Check, 
  Terminal, 
  Server, 
  HardDrive, 
  Cloud, 
  ShieldCheck, 
  FolderTree, 
  Play, 
  CheckCircle2, 
  Search,
  Maximize2,
  Minimize2,
  Code2,
  BookOpen,
  Layers,
  FileText
} from 'lucide-react';
import { CI4_CODE_FILES } from '../data/initialData';
import { CI4FileEntry } from '../types';

export const DevOpsHub: React.FC = () => {
  const [selectedFile, setSelectedFile] = useState<CI4FileEntry>(CI4_CODE_FILES[0]);
  const [copiedFile, setCopiedFile] = useState<string | null>(null);
  const [copiedAll, setCopiedAll] = useState(false);
  const [isZipping, setIsZipping] = useState(false);
  const [zipSuccess, setZipSuccess] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [categoryFilter, setCategoryFilter] = useState<string>('all');
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [viewMode, setViewMode] = useState<'inspector' | 'all-codes'>('inspector');

  // Diagnostic simulator state
  const [diagnosticStatus, setDiagnosticStatus] = useState<Record<string, 'idle' | 'running' | 'passed'>>({
    routing: 'idle',
    tmpWritable: 'idle',
    dbEnv: 'idle',
    cloudinary: 'idle',
  });

  // Filtered files
  const filteredFiles = useMemo(() => {
    return CI4_CODE_FILES.filter((file) => {
      const matchesSearch = 
        file.path.toLowerCase().includes(searchQuery.toLowerCase()) ||
        file.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
        file.content.toLowerCase().includes(searchQuery.toLowerCase());
      
      const matchesCategory = 
        categoryFilter === 'all' || 
        file.category === categoryFilter ||
        (categoryFilter === 'devops' && (file.category === 'devops' || file.path.includes('vercel') || file.path.includes('index.php')));

      return matchesSearch && matchesCategory;
    });
  }, [searchQuery, categoryFilter]);

  const handleCopySingle = (file: CI4FileEntry) => {
    navigator.clipboard.writeText(file.content);
    setCopiedFile(file.path);
    setTimeout(() => setCopiedFile(null), 2000);
  };

  const handleCopyAllCode = () => {
    const concatenated = CI4_CODE_FILES.map((file) => {
      return `/* =========================================================================\n * FILE: ${file.path}\n * CATEGORY: ${file.category}\n * PURPOSE: ${file.description}\n * ========================================================================= */\n\n${file.content}\n\n`;
    }).join('\n');

    navigator.clipboard.writeText(concatenated);
    setCopiedAll(true);
    setTimeout(() => setCopiedAll(false), 2500);
  };

  const handleDownloadZip = async () => {
    setIsZipping(true);
    setZipSuccess(false);

    try {
      const zip = new JSZip();

      // Add all core CI4 files into respective directory tree
      CI4_CODE_FILES.forEach((f) => {
        zip.file(f.path, f.content);
      });

      const blob = await zip.generateAsync({ type: 'blob' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'apexstride-codeigniter4-vercel-serverless.zip';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);

      setZipSuccess(true);
      setTimeout(() => setZipSuccess(false), 3000);
    } catch (err) {
      console.error('Failed to generate ZIP', err);
    } finally {
      setIsZipping(false);
    }
  };

  const runDiagnostics = () => {
    setDiagnosticStatus({
      routing: 'running',
      tmpWritable: 'running',
      dbEnv: 'running',
      cloudinary: 'running',
    });

    setTimeout(() => {
      setDiagnosticStatus((prev) => ({ ...prev, routing: 'passed' }));
    }, 400);

    setTimeout(() => {
      setDiagnosticStatus((prev) => ({ ...prev, tmpWritable: 'passed' }));
    }, 800);

    setTimeout(() => {
      setDiagnosticStatus((prev) => ({ ...prev, dbEnv: 'passed' }));
    }, 1200);

    setTimeout(() => {
      setDiagnosticStatus((prev) => ({ ...prev, cloudinary: 'passed' }));
    }, 1600);
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      {/* Header Banner */}
      <div className="bg-stone-900 text-white rounded-3xl p-6 sm:p-8 border border-stone-800 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div className="space-y-2">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
            <Terminal className="w-3.5 h-3.5" />
            <span>Vercel Serverless DevOps & Code Explorer</span>
          </div>
          <h1 className="text-3xl font-black tracking-tight">
            CodeIgniter 4 Serverless Core
          </h1>
          <p className="text-xs sm:text-sm text-stone-400 max-w-2xl leading-relaxed">
            Every file in this project is architected specifically for Vercel's ephemeral, read-only runtime. Inspect all {CI4_CODE_FILES.length} production PHP controllers, models, views, and deployment configurations below.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <button
            onClick={handleCopyAllCode}
            className="px-4 py-3 rounded-2xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-bold text-xs transition-all border border-stone-700 flex items-center gap-2"
          >
            {copiedAll ? (
              <>
                <Check className="w-4 h-4 text-emerald-400" />
                <span className="text-emerald-400">All Code Copied!</span>
              </>
            ) : (
              <>
                <Copy className="w-4 h-4 text-amber-400" />
                <span>Copy Entire Codebase</span>
              </>
            )}
          </button>

          <button
            onClick={handleDownloadZip}
            disabled={isZipping}
            className="px-5 py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs uppercase tracking-wider transition-all shadow-lg hover:shadow-amber-500/20 flex items-center gap-2"
          >
            <Download className="w-4 h-4" />
            <span>{isZipping ? 'Bundling ZIP...' : zipSuccess ? 'Downloaded!' : 'Download CI4 ZIP'}</span>
          </button>
        </div>
      </div>

      {/* 4 Strict Serverless Constraints Matrix */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* 1. vercel.json */}
        <div className="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs flex flex-col justify-between">
          <div>
            <div className="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mb-3">
              <Server className="w-4 h-4" />
            </div>
            <h3 className="text-xs font-black uppercase tracking-wider text-stone-900">
              1. Routing & vercel.json
            </h3>
            <p className="text-xs text-stone-500 mt-1 leading-relaxed">
              Routes static assets directly and hands all HTTP requests to <code className="text-amber-600 font-mono">public/index.php</code> using <code className="font-mono text-stone-800 font-semibold">vercel-php@0.7.0</code>.
            </p>
          </div>
          <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md mt-4 inline-block">
            Strict Spec Satisfied
          </span>
        </div>

        {/* 2. File Uploads */}
        <div className="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs flex flex-col justify-between">
          <div>
            <div className="w-8 h-8 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center mb-3">
              <Cloud className="w-4 h-4" />
            </div>
            <h3 className="text-xs font-black uppercase tracking-wider text-stone-900">
              2. Cloudinary Media Stream
            </h3>
            <p className="text-xs text-stone-500 mt-1 leading-relaxed">
              <code className="text-stone-800 font-mono font-semibold">ImageUploadService</code> intercepts uploads in <code className="text-amber-600 font-mono">/tmp</code> and streams directly to Cloudinary REST API. Zero local disk writes.
            </p>
          </div>
          <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md mt-4 inline-block">
            Strict Spec Satisfied
          </span>
        </div>

        {/* 3. Database */}
        <div className="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs flex flex-col justify-between">
          <div>
            <div className="w-8 h-8 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center mb-3">
              <HardDrive className="w-4 h-4" />
            </div>
            <h3 className="text-xs font-black uppercase tracking-wider text-stone-900">
              3. getenv() Database
            </h3>
            <p className="text-xs text-stone-500 mt-1 leading-relaxed">
              <code className="text-stone-800 font-mono font-semibold">Database.php</code> is driven strictly by environment variables for PlanetScale, Supabase, Neon, or AWS RDS.
            </p>
          </div>
          <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md mt-4 inline-block">
            Strict Spec Satisfied
          </span>
        </div>

        {/* 4. Writable Bypass */}
        <div className="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs flex flex-col justify-between">
          <div>
            <div className="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center mb-3">
              <ShieldCheck className="w-4 h-4" />
            </div>
            <h3 className="text-xs font-black uppercase tracking-wider text-stone-900">
              4. /tmp Writable Bypass
            </h3>
            <p className="text-xs text-stone-500 mt-1 leading-relaxed">
              <code className="text-stone-800 font-mono font-semibold">Paths.php</code> routes cache, logs, and sessions to <code className="text-amber-600 font-mono">/tmp/ci4_writable</code> to avoid read-only disk crashes.
            </p>
          </div>
          <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md mt-4 inline-block">
            Strict Spec Satisfied
          </span>
        </div>
      </div>

      {/* Serverless Diagnostics Runner */}
      <div className="bg-stone-900 text-white rounded-2xl p-6 border border-stone-800 shadow-md">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
          <div>
            <h3 className="text-sm font-bold flex items-center gap-2">
              <Play className="w-4 h-4 text-amber-500" />
              <span>Vercel Serverless Pre-Flight Simulator</span>
            </h3>
            <p className="text-xs text-stone-400 mt-0.5">
              Simulates container initialization, /tmp disk accessibility, and environment variable bindings.
            </p>
          </div>
          <button
            onClick={runDiagnostics}
            className="px-4 py-2 rounded-xl bg-stone-800 hover:bg-stone-700 text-amber-400 border border-stone-700 text-xs font-bold transition-colors"
          >
            Run Diagnostic Checks
          </button>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 text-xs">
          <div className="bg-stone-950 p-3 rounded-xl border border-stone-800 flex items-center justify-between">
            <span className="text-stone-400">vercel-php runtime</span>
            {diagnosticStatus.routing === 'passed' ? (
              <span className="text-emerald-400 font-bold flex items-center gap-1">
                <CheckCircle2 className="w-3.5 h-3.5" /> OK (0.7.0)
              </span>
            ) : diagnosticStatus.routing === 'running' ? (
              <span className="text-amber-400 animate-pulse font-mono">Checking...</span>
            ) : (
              <span className="text-stone-500 font-mono">Ready</span>
            )}
          </div>

          <div className="bg-stone-950 p-3 rounded-xl border border-stone-800 flex items-center justify-between">
            <span className="text-stone-400">/tmp Writable Subdirs</span>
            {diagnosticStatus.tmpWritable === 'passed' ? (
              <span className="text-emerald-400 font-bold flex items-center gap-1">
                <CheckCircle2 className="w-3.5 h-3.5" /> Mounted 0777
              </span>
            ) : diagnosticStatus.tmpWritable === 'running' ? (
              <span className="text-amber-400 animate-pulse font-mono">Checking...</span>
            ) : (
              <span className="text-stone-500 font-mono">Ready</span>
            )}
          </div>

          <div className="bg-stone-950 p-3 rounded-xl border border-stone-800 flex items-center justify-between">
            <span className="text-stone-400">DB getenv() mapping</span>
            {diagnosticStatus.dbEnv === 'passed' ? (
              <span className="text-emerald-400 font-bold flex items-center gap-1">
                <CheckCircle2 className="w-3.5 h-3.5" /> Configured
              </span>
            ) : diagnosticStatus.dbEnv === 'running' ? (
              <span className="text-amber-400 animate-pulse font-mono">Checking...</span>
            ) : (
              <span className="text-stone-500 font-mono">Ready</span>
            )}
          </div>

          <div className="bg-stone-950 p-3 rounded-xl border border-stone-800 flex items-center justify-between">
            <span className="text-stone-400">Cloudinary REST API</span>
            {diagnosticStatus.cloudinary === 'passed' ? (
              <span className="text-emerald-400 font-bold flex items-center gap-1">
                <CheckCircle2 className="w-3.5 h-3.5" /> Direct Stream
              </span>
            ) : diagnosticStatus.cloudinary === 'running' ? (
              <span className="text-amber-400 animate-pulse font-mono">Checking...</span>
            ) : (
              <span className="text-stone-500 font-mono">Ready</span>
            )}
          </div>
        </div>
      </div>

      {/* Code Viewer Mode Selector & Search */}
      <div className="bg-white rounded-3xl border border-stone-200 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        {/* View Mode Tabs */}
        <div className="flex items-center gap-2">
          <button
            onClick={() => setViewMode('inspector')}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 ${
              viewMode === 'inspector'
                ? 'bg-stone-900 text-white shadow-sm'
                : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
            }`}
          >
            <FolderTree className="w-4 h-4" />
            <span>File Inspector ({CI4_CODE_FILES.length} files)</span>
          </button>
          <button
            onClick={() => setViewMode('all-codes')}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 ${
              viewMode === 'all-codes'
                ? 'bg-stone-900 text-white shadow-sm'
                : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
            }`}
          >
            <Code2 className="w-4 h-4 text-amber-500" />
            <span>All Codes Continuous Feed</span>
          </button>
        </div>

        {/* Search Box */}
        <div className="relative flex-grow md:max-w-md">
          <Search className="w-4 h-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Search code files by name, path or content..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-xl bg-stone-50 border border-stone-200 text-xs focus:outline-none focus:border-stone-900"
          />
        </div>
      </div>

      {/* Category Pills */}
      <div className="flex flex-wrap items-center gap-2">
        <button
          onClick={() => setCategoryFilter('all')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            categoryFilter === 'all'
              ? 'bg-stone-900 text-white'
              : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
          }`}
        >
          All ({CI4_CODE_FILES.length})
        </button>
        <button
          onClick={() => setCategoryFilter('devops')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            categoryFilter === 'devops'
              ? 'bg-amber-600 text-white'
              : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
          }`}
        >
          Vercel & DevOps
        </button>
        <button
          onClick={() => setCategoryFilter('config')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            categoryFilter === 'config'
              ? 'bg-amber-600 text-white'
              : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
          }`}
        >
          Config (Routes, DB, Paths)
        </button>
        <button
          onClick={() => setCategoryFilter('controller')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            categoryFilter === 'controller'
              ? 'bg-amber-600 text-white'
              : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
          }`}
        >
          Controllers
        </button>
        <button
          onClick={() => setCategoryFilter('model')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            categoryFilter === 'model'
              ? 'bg-amber-600 text-white'
              : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
          }`}
        >
          Models
        </button>
        <button
          onClick={() => setCategoryFilter('view')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            categoryFilter === 'view'
              ? 'bg-amber-600 text-white'
              : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
          }`}
        >
          Views
        </button>
        <button
          onClick={() => setCategoryFilter('library')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            categoryFilter === 'library'
              ? 'bg-amber-600 text-white'
              : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
          }`}
        >
          Libraries & Filters
        </button>
        <button
          onClick={() => setCategoryFilter('sql')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            categoryFilter === 'sql'
              ? 'bg-amber-600 text-white'
              : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
          }`}
        >
          Database SQL
        </button>
      </div>

      {/* VIEW MODE 1: Interactive Single-File Inspector */}
      {viewMode === 'inspector' && (
        <div className={`grid grid-cols-1 lg:grid-cols-12 gap-6 items-start ${isFullscreen ? 'fixed inset-0 z-50 bg-stone-900 p-6 overflow-auto' : ''}`}>
          {/* File Navigator Sidebar */}
          <div className="lg:col-span-4 bg-white rounded-3xl border border-stone-200 p-4 shadow-xs space-y-3">
            <div className="flex items-center justify-between pb-2 border-b border-stone-200 px-2">
              <div className="flex items-center gap-2">
                <FolderTree className="w-4 h-4 text-stone-700" />
                <h3 className="text-xs font-black uppercase tracking-wider text-stone-900">
                  Project Files ({filteredFiles.length})
                </h3>
              </div>
            </div>

            <div className="space-y-1 max-h-[580px] overflow-y-auto pr-1">
              {filteredFiles.map((file) => {
                const isSelected = selectedFile.path === file.path;
                return (
                  <button
                    key={file.path}
                    onClick={() => setSelectedFile(file)}
                    className={`w-full text-left px-3 py-2.5 rounded-xl text-xs transition-all flex items-start gap-2.5 ${
                      isSelected
                        ? 'bg-stone-900 text-white font-bold shadow-xs'
                        : 'hover:bg-stone-100 text-stone-700 font-medium'
                    }`}
                  >
                    <FileCode
                      className={`w-4 h-4 shrink-0 mt-0.5 ${
                        isSelected ? 'text-amber-400' : 'text-stone-400'
                      }`}
                    />
                    <div className="truncate">
                      <div className="font-mono text-[11px] truncate">{file.path}</div>
                      <div
                        className={`text-[10px] truncate ${
                          isSelected ? 'text-stone-400' : 'text-stone-500'
                        }`}
                      >
                        {file.description}
                      </div>
                    </div>
                  </button>
                );
              })}

              {filteredFiles.length === 0 && (
                <div className="p-4 text-center text-xs text-stone-400">
                  No files match "{searchQuery}"
                </div>
              )}
            </div>
          </div>

          {/* Code Content Viewer */}
          <div className="lg:col-span-8 bg-stone-950 rounded-3xl border border-stone-800 shadow-2xl overflow-hidden flex flex-col">
            {/* Viewer Toolbar */}
            <div className="bg-stone-900 px-6 py-3 border-b border-stone-800 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <span className="w-3 h-3 rounded-full bg-rose-500 inline-block" />
                <span className="w-3 h-3 rounded-full bg-amber-500 inline-block" />
                <span className="w-3 h-3 rounded-full bg-emerald-500 inline-block" />
                <span className="text-xs font-mono text-stone-300 font-bold pl-2">
                  {selectedFile.path}
                </span>
                <span className="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-stone-800 text-amber-400 border border-stone-700">
                  {selectedFile.category}
                </span>
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => setIsFullscreen(!isFullscreen)}
                  className="p-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs transition-colors"
                  title={isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'}
                >
                  {isFullscreen ? <Minimize2 className="w-3.5 h-3.5" /> : <Maximize2 className="w-3.5 h-3.5" />}
                </button>

                <button
                  onClick={() => handleCopySingle(selectedFile)}
                  className="px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-semibold flex items-center gap-1.5 transition-colors border border-stone-700"
                >
                  {copiedFile === selectedFile.path ? (
                    <>
                      <Check className="w-3.5 h-3.5 text-emerald-400" />
                      <span className="text-emerald-400">Copied!</span>
                    </>
                  ) : (
                    <>
                      <Copy className="w-3.5 h-3.5" />
                      <span>Copy Code</span>
                    </>
                  )}
                </button>
              </div>
            </div>

            {/* Description banner */}
            <div className="bg-stone-900/60 px-6 py-2.5 text-xs text-stone-400 border-b border-stone-800/80 flex items-center justify-between">
              <span>{selectedFile.description}</span>
              <span className="font-mono text-[11px] text-stone-500">
                {selectedFile.content.split('\n').length} lines
              </span>
            </div>

            {/* Code Text with Line Numbers */}
            <div className="p-6 overflow-x-auto max-h-[580px] font-mono text-xs text-stone-200 leading-relaxed flex">
              {/* Line numbers column */}
              <div className="select-none text-stone-600 text-right pr-4 border-r border-stone-800/80 mr-4 font-mono">
                {selectedFile.content.split('\n').map((_, idx) => (
                  <div key={idx}>{idx + 1}</div>
                ))}
              </div>
              {/* Code */}
              <pre className="flex-1 overflow-x-auto">
                <code>{selectedFile.content}</code>
              </pre>
            </div>
          </div>
        </div>
      )}

      {/* VIEW MODE 2: Continuous "All Codes" Master Feed */}
      {viewMode === 'all-codes' && (
        <div className="space-y-6">
          <div className="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 text-xs text-amber-900 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <BookOpen className="w-4 h-4 text-amber-600 shrink-0" />
              <span>
                <strong>All Codes Master View:</strong> Showing all {filteredFiles.length} files in sequence. You can copy individual files below or click "Copy Entire Codebase" above.
              </span>
            </div>
            <button
              onClick={handleCopyAllCode}
              className="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs"
            >
              {copiedAll ? 'Copied Entire Project!' : 'Copy All Codebase'}
            </button>
          </div>

          {filteredFiles.map((file, index) => (
            <div
              key={file.path}
              id={`file-${file.path.replace(/[^a-zA-Z0-9]/g, '-')}`}
              className="bg-stone-950 rounded-3xl border border-stone-800 shadow-xl overflow-hidden"
            >
              {/* Header */}
              <div className="bg-stone-900 px-6 py-3 border-b border-stone-800 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <span className="text-amber-500 font-bold font-mono text-xs">#{index + 1}</span>
                  <span className="text-xs font-mono text-white font-bold">{file.path}</span>
                  <span className="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-stone-800 text-amber-400 border border-stone-700">
                    {file.category}
                  </span>
                </div>

                <div className="flex items-center gap-3">
                  <span className="text-[11px] text-stone-400 font-mono hidden sm:inline">
                    {file.content.split('\n').length} lines
                  </span>
                  <button
                    onClick={() => handleCopySingle(file)}
                    className="px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-semibold flex items-center gap-1.5 transition-colors border border-stone-700"
                  >
                    {copiedFile === file.path ? (
                      <>
                        <Check className="w-3.5 h-3.5 text-emerald-400" />
                        <span className="text-emerald-400">Copied!</span>
                      </>
                    ) : (
                      <>
                        <Copy className="w-3.5 h-3.5" />
                        <span>Copy File</span>
                      </>
                    )}
                  </button>
                </div>
              </div>

              {/* Description */}
              <div className="bg-stone-900/40 px-6 py-2 text-xs text-stone-400 border-b border-stone-800/80">
                {file.description}
              </div>

              {/* Code */}
              <div className="p-6 overflow-x-auto max-h-[400px] font-mono text-xs text-stone-200 leading-relaxed flex">
                <div className="select-none text-stone-600 text-right pr-4 border-r border-stone-800/80 mr-4 font-mono">
                  {file.content.split('\n').map((_, idx) => (
                    <div key={idx}>{idx + 1}</div>
                  ))}
                </div>
                <pre className="flex-1 overflow-x-auto">
                  <code>{file.content}</code>
                </pre>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};
