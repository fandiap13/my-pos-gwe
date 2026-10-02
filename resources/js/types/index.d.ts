export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at?: string;
    role: 'admin' | 'kasir';
    is_active: boolean;
}

// Flash message dari redirect backend (->with('success'|'error', ...)) —
// di-share oleh HandleInertiaRequests, ditampilkan sebagai toast.
export interface FlashProps {
    success?: string | null;
    error?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    // Status shift kasir — di-share HandleInertiaRequests untuk semua
    // request (null untuk admin). Dipakai KasirLayout (badge & link
    // Tutup Shift).
    shiftIsActive?: boolean | null;
    flash?: FlashProps;
};
