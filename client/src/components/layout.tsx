import { Link, useLocation } from "wouter";
import { useLanguage } from "@/lib/i18n";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from "@/components/ui/select";
import { Menu, X, Phone, Mail, MapPin, Facebook, Instagram } from "lucide-react";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import WhatsAppButton from "@/components/whatsapp-button";

export default function Layout({ children }: { children: React.ReactNode }) {
  const { t, language, setLanguage } = useLanguage();
  const [location] = useLocation();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const navLinks = [
    { href: "/", label: t.nav.home },
    { href: "/about", label: t.nav.about },
    { href: "/services", label: t.nav.services },
    { href: "/contact", label: t.nav.contact },
  ];

  return (
    <div className="min-h-screen flex flex-col font-sans">
      {/* Top Bar */}
      <div className="bg-primary text-primary-foreground py-2 text-sm hidden md:block">
        <div className="container mx-auto px-4 flex justify-between items-center">
          <div className="flex items-center gap-6">
            <span className="flex items-center gap-2">
              <Phone className="w-4 h-4" /> 077 731 7589
            </span>
            <span className="flex items-center gap-2">
              <Mail className="w-4 h-4" /> sinathtravels@outlook.com
            </span>
          </div>
          <div className="flex items-center gap-4">
            <span>IATA Accredited Agent</span>
            <div className="flex gap-2">
              <Facebook className="w-4 h-4 cursor-pointer hover:text-accent transition-colors" />
              <Instagram className="w-4 h-4 cursor-pointer hover:text-accent transition-colors" />
            </div>
          </div>
        </div>
      </div>

      {/* Main Header */}
      <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-border shadow-sm">
        <div className="container mx-auto px-4 h-20 flex justify-between items-center">
          {/* Logo - FIXED: Removed nested <a> tag */}
          <Link href="/">
            <div className="flex items-center gap-2 cursor-pointer">
              <div className="text-2xl font-heading font-black tracking-tighter text-primary">
                SI<span className="text-secondary">N</span>ATH
              </div>
              <div className="hidden lg:block text-xs font-medium text-muted-foreground uppercase tracking-widest border-l pl-2 border-border">
                Travels & Tours
              </div>
            </div>
          </Link>

          {/* Desktop Nav */}
          <nav className="hidden md:flex items-center gap-8">
            {navLinks.map((link) => (
              <Link key={link.href} href={link.href}>
                <span className={`text-sm font-semibold transition-colors hover:text-secondary cursor-pointer ${location === link.href ? "text-secondary" : "text-foreground"
                  }`}>
                  {link.label}
                </span>
              </Link>
            ))}

            <Select value={language} onValueChange={(v: any) => setLanguage(v)}>
              <SelectTrigger className="w-[100px] h-8 text-xs bg-muted border-none">
                <SelectValue placeholder="Language" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="en">English</SelectItem>
                <SelectItem value="si">Sinhala</SelectItem>
                <SelectItem value="ta">Tamil</SelectItem>
              </SelectContent>
            </Select>

            <Link href="/contact">
              <Button size="sm" className="bg-secondary hover:bg-secondary/90 text-white font-bold rounded-full px-6">
                {t.nav.bookNow}
              </Button>
            </Link>
          </nav>

          {/* Mobile Menu Toggle */}
          <button
            className="md:hidden p-2 text-primary"
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          >
            {isMobileMenuOpen ? <X /> : <Menu />}
          </button>
        </div>

        {/* Mobile Menu */}
        {isMobileMenuOpen && (
          <div className="md:hidden bg-background border-b border-border absolute w-full px-4 py-4 flex flex-col gap-4 shadow-xl animate-in slide-in-from-top-5">
            {navLinks.map((link) => (
              <Link key={link.href} href={link.href}>
                <span
                  className={`text-lg font-medium py-2 block ${location === link.href ? "text-secondary" : "text-foreground"
                    }`}
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  {link.label}
                </span>
              </Link>
            ))}
            <div className="py-2 border-t border-border flex justify-between items-center">
              <span className="text-sm text-muted-foreground">Language</span>
              <div className="flex gap-2">
                <button onClick={() => setLanguage("en")} className={`px-2 py-1 text-xs rounded ${language === 'en' ? 'bg-primary text-white' : 'bg-muted'}`}>EN</button>
                <button onClick={() => setLanguage("si")} className={`px-2 py-1 text-xs rounded ${language === 'si' ? 'bg-primary text-white' : 'bg-muted'}`}>SI</button>
                <button onClick={() => setLanguage("ta")} className={`px-2 py-1 text-xs rounded ${language === 'ta' ? 'bg-primary text-white' : 'bg-muted'}`}>TA</button>
              </div>
            </div>
          </div>
        )}
      </header>

      {/* Main Content */}
      <main className="flex-grow">
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-primary text-primary-foreground pt-16 pb-8">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div className="space-y-4">
              <h3 className="text-2xl font-heading font-bold text-white">SINATH</h3>
              <p className="text-primary-foreground/80 text-sm leading-relaxed">
                Your trusted IATA accredited partner for air ticketing, visas, and tour packages.
              </p>
              <div className="bg-white/10 p-4 rounded-lg inline-block backdrop-blur-sm border border-white/20">
                <div className="font-bold text-accent">IATA ACCREDITED</div>
                <div className="text-xs text-white/70">Official Agent</div>
              </div>
            </div>

            <div>
              <h4 className="font-bold text-lg mb-6 text-white">Quick Links</h4>
              <ul className="space-y-3 text-sm text-primary-foreground/80">
                <li><Link href="/"><span className="hover:text-accent transition-colors cursor-pointer">Home</span></Link></li>
                <li><Link href="/about"><span className="hover:text-accent transition-colors cursor-pointer">About Us</span></Link></li>
                <li><Link href="/services"><span className="hover:text-accent transition-colors cursor-pointer">Services</span></Link></li>
                <li><Link href="/contact"><span className="hover:text-accent transition-colors cursor-pointer">Contact</span></Link></li>
              </ul>
            </div>

            <div>
              <h4 className="font-bold text-lg mb-6 text-white">Newsletter</h4>
              <p className="text-sm text-primary-foreground/80 mb-4">
                Subscribe to get special offers and travel inspiration.
              </p>
              <form className="space-y-2" onSubmit={(e) => e.preventDefault()}>
                <input
                  type="email"
                  placeholder="Your email"
                  className="w-full px-3 py-2 text-sm rounded bg-white/10 border border-white/20 text-white placeholder:text-white/50 focus:outline-none focus:border-accent"
                />
                <Button className="w-full bg-secondary hover:bg-secondary/90 text-white font-bold">
                  Subscribe
                </Button>
              </form>
            </div>

            <div>
              <h4 className="font-bold text-lg mb-6 text-white">Contact</h4>
              <ul className="space-y-4 text-sm text-primary-foreground/80">
                <li className="flex items-start gap-3">
                  <MapPin className="w-5 h-5 text-secondary shrink-0" />
                  <span>Main Street, Akkaraipattu, Sri Lanka</span>
                </li>
                <li className="flex items-center gap-3">
                  <Phone className="w-5 h-5 text-secondary shrink-0" />
                  <span>0777317589</span>
                </li>
                <li className="flex items-center gap-3">
                  <Mail className="w-5 h-5 text-secondary shrink-0" />
                  <span>sinathtravels@outlook.com</span>
                </li>
              </ul>
            </div>
          </div>

          <div className="border-t border-white/10 pt-8 text-center text-sm text-primary-foreground/60">
            &copy; {new Date().getFullYear()} Sinath Travels and Tours. {t.footer.rights}
          </div>
          <div className="border-t border-white/10 pt-2 text-center text-sm text-primary">
            <Link href="/admin/login"><span className="hover:text-accent transition-colors cursor-pointer">Admin Login</span></Link>
          </div>
        </div>
      </footer>
      <WhatsAppButton />
    </div>
  );
}