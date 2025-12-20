import { useLanguage } from "@/lib/i18n";
import { services, packages, images } from "@/lib/data";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Link } from "wouter";
import { Plane, FileCheck, Map, ArrowRight } from "lucide-react";

export default function Services() {
  const { t } = useLanguage();

  const iconMap = {
    "Plane": <Plane className="w-12 h-12 text-secondary mb-4" />,
    "FileCheck": <FileCheck className="w-12 h-12 text-secondary mb-4" />,
    "Map": <Map className="w-12 h-12 text-secondary mb-4" />,
  };

  return (
    <div className="py-12 md:py-20 bg-muted/30">
      <div className="container mx-auto px-4">
        <div className="text-center max-w-3xl mx-auto mb-16">
          <h1 className="text-4xl md:text-5xl font-heading font-extrabold text-primary mb-6">
            {t.services.title}
          </h1>
          <p className="text-xl text-muted-foreground">
            {t.services.desc}
          </p>
        </div>

        {/* Main Services Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
          {services.map((service) => (
            <Card key={service.id} className="border-none shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 bg-white">
              <CardContent className="pt-8 text-center flex flex-col items-center">
                {iconMap[service.icon as keyof typeof iconMap]}
                <h3 className="text-2xl font-bold text-primary mb-4">{service.title}</h3>
                <p className="text-muted-foreground">{service.description}</p>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Packages Section */}
        <div className="mb-16">
          <div className="flex items-center justify-between mb-8">
            <h2 className="text-3xl font-heading font-bold text-primary">Exclusive Tour Packages</h2>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {packages.map((pkg) => (
              <Card key={pkg.id} className="overflow-hidden border-none shadow-md group">
                <div className="h-64 overflow-hidden relative">
                   <div className="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors z-10" />
                  <img 
                    src={pkg.image} 
                    alt={pkg.title} 
                    className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                  />
                  <div className="absolute bottom-4 left-4 right-4 z-20">
                     <span className="bg-secondary text-white text-xs font-bold px-3 py-1 rounded-full">{pkg.duration}</span>
                  </div>
                </div>
                <CardHeader>
                  <CardTitle className="text-xl font-bold text-primary">{pkg.title}</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground text-sm mb-4">{pkg.description}</p>
                  <div className="text-2xl font-bold text-secondary">{pkg.price}</div>
                </CardContent>
                <CardFooter>
                   <Link href="/contact" className="w-full">
                    <Button className="w-full bg-primary hover:bg-primary/90">Book Now</Button>
                   </Link>
                </CardFooter>
              </Card>
            ))}
          </div>
        </div>

        {/* Visa Section */}
        <div className="bg-white rounded-3xl p-8 md:p-12 shadow-xl flex flex-col md:flex-row items-center gap-12 border border-border">
          <div className="flex-1 space-y-6">
            <h2 className="text-3xl font-heading font-bold text-primary">Hassle-Free Visa Services</h2>
            <p className="text-muted-foreground text-lg">
              Navigating visa requirements can be complex. Let our experts handle the paperwork for you. We specialize in tourist, business, and transit visas for major destinations including Dubai, Singapore, Malaysia, Thailand, and Europe.
            </p>
            <Link href="/contact">
              <Button size="lg" className="bg-secondary hover:bg-secondary/90">Contact for Visa Assistance</Button>
            </Link>
          </div>
          <div className="flex-1">
            <img src={images.visa} alt="Visa documents" className="rounded-2xl shadow-lg w-full object-cover h-[300px]" />
          </div>
        </div>
      </div>
    </div>
  );
}
