export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at?: string;
    role: 'admin' | 'kasir';
    is_active: boolean;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
