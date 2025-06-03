import React from 'react';
import { motion } from 'framer-motion';
import { Box, Typography } from '@mui/material';

export default function Notification({ message, onClose }) {
    return (
        <motion.div
            initial={{ x: 300, opacity: 0 }}
            animate={{ x: 0, opacity: 1 }}
            exit={{ x: 300, opacity: 0 }}
            transition={{ duration: 0.5 }}
            style={{
                position: 'fixed',
                top: 20,
                right: 20,
                zIndex: 9999
            }}
        >
            <Box sx={{
                bgcolor: 'success.main',
                color: 'white',
                px: 3,
                py: 2,
                borderRadius: 2,
                boxShadow: 3
            }}>
                <Typography>{message}</Typography>
            </Box>
        </motion.div>
    )
}
