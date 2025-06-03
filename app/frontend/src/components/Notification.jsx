import React, { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Box, Typography, IconButton } from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';

export default function Notification({ message, type = 'success', onClose }) {
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        const timer = setTimeout(() => {
            setVisible(false); // inicia animação de saída
        }, 3000);
        return () => clearTimeout(timer);
    }, []);

    const getColor = () => {
        switch (type) {
            case 'error': return 'error.main';
            case 'warning': return 'warning.main';
            default: return 'success.main';
        }
    };

    const handleClose = () => {
        setVisible(false); // animação primeiro
    }

    return (
        <AnimatePresence onExitComplete={onClose}>
            {visible && (
                <motion.div
                    initial={{ x: 300, opacity: 0 }}
                    animate={{ x: 0, opacity: 1 }}
                    exit={{ x: 300, opacity: 0 }}
                    transition={{ duration: 0.5 }}
                >
                    <Box sx={{
                        bgcolor: getColor(),
                        color: 'white',
                        px: 3,
                        py: 2,
                        borderRadius: 2,
                        boxShadow: 3,
                        display: 'flex',
                        alignItems: 'center',
                    }}>
                        <Typography sx={{ flexGrow: 1 }}>{message}</Typography>
                        <IconButton onClick={handleClose} sx={{ color: 'white' }}>
                            <CloseIcon />
                        </IconButton>
                    </Box>
                </motion.div>
            )}
        </AnimatePresence>
    )
}
