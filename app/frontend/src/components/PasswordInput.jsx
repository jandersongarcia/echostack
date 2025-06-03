import React, { useState, useEffect } from 'react';
import { TextField, InputAdornment, IconButton, LinearProgress, Box, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import Visibility from '@mui/icons-material/Visibility';
import VisibilityOff from '@mui/icons-material/VisibilityOff';

// Função pura: pode ficar fora pois não depende de hooks
const calculateStrength = (password) => {
    let strength = 0;
    if (password.length > 5) strength += 1;
    if (password.length > 8) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    return strength;
};

const getStrengthColor = (strength) => {
    switch (strength) {
        case 0: return 'error';
        case 1: return 'error';
        case 2: return 'warning';
        case 3: return 'info';
        case 4:
        case 5: return 'success';
        default: return 'primary';
    }
};

export default function PasswordInput({ label, value, onChange, required = false }) {
    const [showPassword, setShowPassword] = useState(false);
    const [strength, setStrength] = useState(0);
    const { t } = useTranslation();

    useEffect(() => {
        setStrength(calculateStrength(value));
    }, [value]);

    // Agora usamos o t() no local correto, dentro do componente
    const getStrengthLabel = (strength) => {
        switch (strength) {
            case 0: return t('very_weak');
            case 1: return t('weak');
            case 2: return t('average');
            case 3: return t('strong');
            case 4:
            case 5: return t('very_strong');
            default: return '';
        }
    };

    return (
        <Box>
            <TextField
                fullWidth
                type={showPassword ? 'text' : 'password'}
                label={label}
                value={value}
                onChange={onChange}
                required={required}
                autoComplete="new-password"
                InputProps={{
                    endAdornment: (
                        <InputAdornment position="end">
                            <IconButton onClick={() => setShowPassword(!showPassword)} edge="end">
                                {showPassword ? <VisibilityOff /> : <Visibility />}
                            </IconButton>
                        </InputAdornment>
                    )
                }}
            />
            <Box mt={1}>
                <LinearProgress 
                    variant="determinate" 
                    value={(strength / 5) * 100} 
                    color={getStrengthColor(strength)} 
                    sx={{ height: 8, borderRadius: 2 }}
                />
                <Typography variant="caption" sx={{ color: 'text.secondary' }}>
                    {getStrengthLabel(strength)}
                </Typography>
            </Box>
        </Box>
    );
}
