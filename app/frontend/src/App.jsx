import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import LoginPage from './pages/Login/LoginPage';
import AccountPage from './pages/Account/AccountPage';
import AccountRecoveryPage from './pages/AccountRecovery/AccountRecoveryPage';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<LoginPage />} />
        <Route path="/register" element={<AccountPage />} />
        <Route path="/recovery" element={<AccountRecoveryPage />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
