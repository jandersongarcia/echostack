import React from 'react';
import LanguageSelector from './LanguageSelector';
import logo from '../assets/icon-filedow.png';

export default function NavbarLogin() {
  return (
    <div style={{ 
      position: 'absolute', 
      top: 0,  // como estamos fixando a altura total
      left: 0, 
      right: 0, 
      height: '60px',
      display: 'flex', 
      justifyContent: 'space-between', 
      alignItems: 'center',
      padding: '0 20px',
      borderBottom: '1px solid #ddd',
      backgroundColor: '#fff',  // opcional, ajuda no visual
      zIndex: 10
    }}>
      <div>
        <img src={logo} alt="Logo" style={{ height: '40px' }} />
      </div>
      <LanguageSelector />
    </div>
  );
}
