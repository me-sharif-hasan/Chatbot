document.addEventListener('DOMContentLoaded', (event) => {
  // Find all code blocks that highlight.js should process.
  // This could be all <pre><code> blocks, or you could be more specific
  // if you only want to highlight blocks with a language class.
  // For now, let's stick to the standard hljs.highlightAll() which looks for <pre><code>.
  hljs.highlightAll();
});
