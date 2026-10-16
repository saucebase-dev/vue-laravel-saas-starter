export interface PaginatedPosts {
    data: Modules.Blog.Data.PostData[];
    current_page: number;
    /** This listing's URL without the page number: the blog, a category or a tag. */
    path: string;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

/** A category or tag page's title. Absent on the blog index, which titles itself. */
export interface ListingHeading {
    title: string;
    description: string;
}
