import { useState } from "react";
import { useLanguage } from "@/lib/i18n";
import { images } from "@/lib/data";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Link } from "wouter";
import { Plane, FileCheck, Map, ArrowRight, Search, Filter } from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { getServices, getPackages, formatPrice } from "@/lib/api";
import { Skeleton } from "@/components/ui/skeleton";

export default function Services() {
  const { t } = useLanguage();
  const [searchTerm, setSearchTerm] = useState("");
  const [filterCategory, setFilterCategory] = useState("all");

  // Fetch Services
  const { data: services = [], isLoading: isServicesLoading } = useQuery({
    queryKey: ['services'],
    queryFn: getServices,
  });

  // Fetch Packages
  const { data: packages = [], isLoading: isPackagesLoading } = useQuery({
    queryKey: ['packages'],
    queryFn: () => getPackages({ status: 'active' }),
  });

  // Filter Logic
  const filteredPackages = packages.filter(pkg => {
    const matchesSearch = pkg.title_en.toLowerCase().includes(searchTerm.toLowerCase()) ||
      pkg.description_en.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesCategory = filterCategory === "all" || pkg.category === filterCategory;
    return matchesSearch && matchesCategory;
  });

  const iconMap = {
    "Plane": <Plane className="w-12 h-12 text-secondary mb-4" />,
    "FileCheck": <FileCheck className="w-12 h-12 text-secondary mb-4" />,
    "Map": <Map className="w-12 h-12 text-secondary mb-4" />,
  };

  return (
    <div className="bg-background min-h-screen">
      {/* Hero Section */}
      <div className="bg-primary pt-32 pb-20 text-center text-white relative overflow-hidden">
        <div className="absolute inset-0 opacity-10 bg-[url('https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?ixlib=rb-4.0.3')] bg-cover bg-center" />
        <div className="container mx-auto px-4 relative z-10">
          <h1 className="text-4xl md:text-6xl font-heading font-extrabold mb-6 animate-in fade-in slide-in-from-bottom-6 duration-700">
            {t.services.title}
          </h1>
          <p className="text-xl text-primary-foreground/90 max-w-2xl mx-auto animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
            {t.services.desc}
          </p>
        </div>
      </div>

      <div className="container mx-auto px-4 py-16">
        {/* Main Services Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
          {isServicesLoading ? (
            [1, 2, 3].map(i => <Skeleton key={i} className="h-64" />)
          ) : (
            services.map((service) => (
              <Card key={service.id} className="border-none shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 bg-white group">
                <CardContent className="pt-8 text-center flex flex-col items-center">
                  <div className="bg-primary/5 p-4 rounded-full mb-6 group-hover:bg-primary/10 transition-colors">
                    {iconMap[service.icon as keyof typeof iconMap] || <Map className="w-12 h-12 text-secondary" />}
                  </div>
                  <h3 className="text-2xl font-bold text-primary mb-4">{service.title_en}</h3>
                  <p className="text-muted-foreground">{service.description_en}</p>
                </CardContent>
              </Card>
            ))
          )}
        </div>

        {/* Packages Section */}
        <div className="mb-16 scroll-mt-24" id="packages">
          <div className="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
              <h2 className="text-3xl font-heading font-bold text-primary mb-2">Explore Packages</h2>
              <p className="text-muted-foreground">Find the perfect itinerary for your next adventure.</p>
            </div>

            {/* Filter Bar */}
            <div className="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
              <div className="relative w-full md:w-64">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground w-4 h-4" />
                <Input
                  placeholder="Search destinations..."
                  className="pl-9"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
              </div>
              <Select value={filterCategory} onValueChange={setFilterCategory}>
                <SelectTrigger className="w-full md:w-[180px]">
                  <div className="flex items-center gap-2">
                    <Filter className="w-4 h-4" />
                    <SelectValue placeholder="All Categories" />
                  </div>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Categories</SelectItem>
                  <SelectItem value="tour">Tours</SelectItem>
                  <SelectItem value="visa">Visa Services</SelectItem>
                  <SelectItem value="ticket">Ticketing</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {isPackagesLoading ? (
              [1, 2, 3, 4, 5, 6].map(i => (
                <Card key={i} className="overflow-hidden">
                  <Skeleton className="h-64 w-full" />
                  <CardHeader><Skeleton className="h-6 w-3/4" /></CardHeader>
                  <CardContent><Skeleton className="h-4 w-full mb-2" /><Skeleton className="h-4 w-1/2" /></CardContent>
                </Card>
              ))
            ) : filteredPackages.length === 0 ? (
              <div className="col-span-full text-center py-20 bg-muted/50 rounded-lg border border-dashed text-muted-foreground">
                <Map className="w-12 h-12 mx-auto mb-4 opacity-20" />
                <p>No packages found matching your criteria.</p>
                <Button variant="link" onClick={() => { setSearchTerm(''); setFilterCategory('all'); }}>Clear Filters</Button>
              </div>
            ) : (
              filteredPackages.map((pkg) => (
                <Card key={pkg.id} className="overflow-hidden border-none shadow-md group h-full flex flex-col hover:shadow-xl transition-all duration-300">
                  <div className="h-64 overflow-hidden relative shrink-0">
                    <div className="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors z-10" />
                    {pkg.image ? (
                      <img
                        src={pkg.image}
                        alt={pkg.title_en}
                        className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                      />
                    ) : (
                      <div className="w-full h-full bg-muted flex items-center justify-center">
                        <Map className="w-12 h-12 text-muted-foreground" />
                      </div>
                    )}
                    <div className="absolute top-4 left-4 z-20">
                      <span className="bg-white/90 backdrop-blur text-primary text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        {pkg.category}
                      </span>
                    </div>
                  </div>
                  <CardHeader>
                    <CardTitle className="text-xl font-bold text-primary leading-tight">{pkg.title_en}</CardTitle>
                  </CardHeader>
                  <CardContent className="flex-grow">
                    <p className="text-muted-foreground text-sm line-clamp-3 mb-4">{pkg.description_en}</p>
                    <div className="text-2xl font-bold text-secondary">{formatPrice(pkg.price)}</div>
                  </CardContent>
                  <CardFooter className="pt-0">
                    <Link href={`/contact`} className="w-full">
                      <Button className="w-full bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20">Book Now</Button>
                    </Link>
                  </CardFooter>
                </Card>
              ))
            )}
          </div>
        </div>

        {/* Visa Section */}
        <div className="bg-white rounded-3xl p-8 md:p-12 shadow-2xl flex flex-col md:flex-row items-center gap-12 border border-border relative overflow-hidden">
          <div className="absolute inset-0 bg-secondary/5 z-0" />
          <div className="flex-1 space-y-6 relative z-10">
            <h2 className="text-3xl font-heading font-bold text-primary">Hassle-Free Visa Services</h2>
            <p className="text-muted-foreground text-lg leading-relaxed">
              Navigating visa requirements can be complex. Let our experts handle the paperwork for you. We specialize in tourist, business, and transit visas for major destinations including <span className="font-semibold text-foreground">Dubai, Singapore, Malaysia, Thailand, and Europe</span>.
            </p>
            <div className="flex gap-4 pt-4">
              <Link href="/contact">
                <Button size="lg" className="bg-secondary hover:bg-secondary/90">Contact for Visa Assistance</Button>
              </Link>
            </div>
          </div>
          <div className="flex-1 relative z-10">
            <div className="relative">
              <div className="absolute inset-0 bg-primary/10 transform rotate-6 rounded-2xl" />
              <img src={images.FreeVisaServicesimg} alt="Visa documents" className="relative rounded-2xl shadow-lg w-full object-cover h-[350px]" />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
