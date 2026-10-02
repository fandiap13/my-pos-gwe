// Shape standar Laravel paginator (LengthAwarePaginator::toArray()) —
// dipakai sebagai tipe Inertia props untuk semua halaman list/index.
// Lihat AGENTS.md: pagination wajib untuk data yang bisa bertambah besar.
export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}
