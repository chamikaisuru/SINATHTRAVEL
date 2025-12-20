import { useLanguage } from "@/lib/i18n";
import { Button } from "@/components/ui/button";
import { ArrowRight, CheckCircle2, Plane, Globe, Map } from "lucide-react";
import { Link } from "wouter";
import { images, packages } from "@/lib/data";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

export default function Home() {
  const { t } = useLanguage();

  return (
    <div className="flex flex-col gap-16 pb-16">
      {/* Hero Section */}
      <section className="relative h-[600px] md:h-[700px] flex items-center justify-center overflow-hidden">
        <div 
          className="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
          style={{ backgroundImage: `url(${images.hero})` }}
        />
        <div className="absolute inset-0 bg-primary/40 z-10 backdrop-blur-[2px]" />
        
        <div className="container mx-auto px-4 z-20 relative text-center text-white space-y-6 max-w-4xl">
          <Badge className="bg-secondary/90 hover:bg-secondary text-white border-none px-4 py-1 text-sm uppercase tracking-wider mb-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            {t.about.iata}
          </Badge>
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-heading font-extrabold leading-tight tracking-tight animate-in fade-in slide-in-from-bottom-8 duration-700 delay-100">
            {t.hero.title}
          </h1>
          <p className="text-lg md:text-xl text-white/90 max-w-2xl mx-auto font-light animate-in fade-in slide-in-from-bottom-8 duration-700 delay-200">
            {t.hero.subtitle}
          </p>
          <div className="pt-8 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-300">
            <Link href="/services">
              <Button size="lg" className="bg-secondary hover:bg-secondary/90 text-white font-bold rounded-full h-14 px-8 text-lg shadow-xl shadow-secondary/20">
                {t.hero.cta} <ArrowRight className="ml-2 w-5 h-5" />
              </Button>
            </Link>
          </div>
        </div>
      </section>

      {/* Services Preview */}
      <section className="container mx-auto px-4 -mt-24 relative z-30">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <ServiceCard 
            icon={<Plane className="w-10 h-10 text-secondary" />} 
            title={t.services.airTicketing}
            desc="Best deals on flights worldwide."
          />
          <ServiceCard 
            icon={<Globe className="w-10 h-10 text-secondary" />} 
            title={t.services.visaServices}
            desc="Expert visa handling for all countries."
          />
          <ServiceCard 
            icon={<Map className="w-10 h-10 text-secondary" />} 
            title={t.services.tourPackages}
            desc="Curated holiday experiences."
          />
        </div>
      </section>

      {/* About Preview */}
      <section className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div className="space-y-6">
            <h2 className="text-3xl md:text-4xl font-heading font-bold text-primary">
              Why Choose <span className="text-secondary">Sinath</span> Travels?
            </h2>
            <p className="text-muted-foreground text-lg leading-relaxed">
              We are a fully licensed IATA accredited travel agency committed to making your travel dreams a reality. With years of experience in the industry, we provide reliable, efficient, and personalized travel solutions.
            </p>
            <ul className="space-y-4">
              {[
                "IATA Accredited Agency",
                "24/7 Customer Support",
                "Competitive Pricing",
                "Experienced Travel Consultants"
              ].map((item, i) => (
                <li key={i} className="flex items-center gap-3 text-foreground font-medium">
                  <CheckCircle2 className="w-5 h-5 text-green-500" />
                  {item}
                </li>
              ))}
            </ul>
            <Link href="/about">
              <Button variant="outline" className="mt-4 border-primary text-primary hover:bg-primary hover:text-white">
                Learn More About Us
              </Button>
            </Link>
          </div>
          <div className="relative">
            <div className="absolute inset-0 bg-secondary/10 rounded-3xl transform rotate-3 scale-105" />
            <img 
              src={images.tourists} 
              alt="Happy travelers" 
              className="relative rounded-3xl shadow-2xl w-full object-cover aspect-[4/3]"
            />
          </div>
        </div>
      </section>

      {/* Featured Packages */}
      <section className="bg-muted/50 py-20">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-heading font-bold text-primary mb-4">Popular Packages</h2>
            <p className="text-muted-foreground max-w-2xl mx-auto">Explore our most sought-after destinations and exclusive holiday deals crafted just for you.</p>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {packages.map((pkg) => (
              <Card key={pkg.id} className="overflow-hidden border-none shadow-lg group hover:shadow-xl transition-all duration-300">
                <div className="h-64 overflow-hidden relative">
                  <div className="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full font-bold text-primary z-10">
                    {pkg.price}
                  </div>
                  <img 
                    src={pkg.image} 
                    alt={pkg.title} 
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                  />
                </div>
                <CardHeader>
                  <CardTitle className="text-xl font-bold text-primary">{pkg.title}</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground text-sm">{pkg.description}</p>
                </CardContent>
                <CardFooter className="flex justify-between items-center">
                  <span className="text-sm font-medium text-muted-foreground">{pkg.duration}</span>
                  <Link href="/contact">
                    <Button variant="ghost" className="text-secondary hover:text-secondary/80 font-semibold p-0 hover:bg-transparent">
                      Inquire Now <ArrowRight className="ml-2 w-4 h-4" />
                    </Button>
                  </Link>
                </CardFooter>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}

function ServiceCard({ icon, title, desc }: { icon: React.ReactNode, title: string, desc: string }) {
  return (
    <div className="bg-white p-8 rounded-xl shadow-lg border border-border/50 hover:border-secondary/50 transition-all duration-300 group">
      <div className="mb-6 bg-primary/5 w-16 h-16 rounded-full flex items-center justify-center group-hover:bg-primary/10 transition-colors">
        {icon}
      </div>
      <h3 className="text-xl font-bold text-primary mb-3">{title}</h3>
      <p className="text-muted-foreground">{desc}</p>
    </div>
  );
}
