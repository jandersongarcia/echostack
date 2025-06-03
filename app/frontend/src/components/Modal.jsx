import React from 'react';

export default function Modal({ visible, title, onClose, children }) {
  if (!visible) return null;

  const containerStyle = {
    position: 'fixed',
    top: 0, left: 0, right: 0, bottom: 0,
    backgroundColor: 'rgba(0,0,0,0.5)',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 999
  };

  const modalStyle = {
    backgroundColor: '#fff',
    padding: '20px 30px',
    borderRadius: '8px',
    minWidth: '300px',
    maxWidth: '80%',
    maxHeight: '80vh',
    overflowY: 'auto'
  };

  return (
    <div style={containerStyle}>
      <div style={modalStyle}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <h3>{title}</h3>
          <button onClick={onClose} style={{ fontSize: '18px', border: 'none', background: 'none', cursor: 'pointer' }}>✕</button>
        </div>

        <div>
          {children}
        </div>

        <div style={{ marginTop: '20px', textAlign: 'right' }}>
          <button onClick={onClose} style={{
            padding: '8px 16px',
            backgroundColor: '#ddd',
            border: 'none',
            borderRadius: '4px',
            cursor: 'pointer'
          }}>
            Fechar
          </button>
        </div>
      </div>
    </div>
  );
}
