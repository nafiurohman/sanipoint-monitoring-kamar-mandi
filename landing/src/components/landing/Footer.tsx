import siteData from '@/data/siteData.json';

export function Footer() {
  return (
    <footer className="py-12 border-t border-border/50">
      <div className="container mx-auto px-4 sm:px-6">
        <div className="flex flex-col md:flex-row items-center justify-between gap-6">
          {/* Logo & Copyright */}
          <div className="flex items-center gap-3">
            <span className="text-2xl">{siteData.brand.logo}</span>
            <span className="text-muted-foreground text-sm">
              {siteData.footer.copyright}
            </span>
          </div>

          {/* Links */}
          <div className="flex items-center gap-6">
            {siteData.footer.links.map((link) => (
              <a
                key={link.label}
                href={link.href}
                className="text-sm text-muted-foreground hover:text-foreground transition-colors"
              >
                {link.label}
              </a>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
}
