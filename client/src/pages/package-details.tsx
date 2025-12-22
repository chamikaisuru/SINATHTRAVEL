import { useRoute, useLocation } from "wouter";
import { useQuery } from "@tanstack/react-query";
import { getPackages, formatPrice } from "@/lib/api";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { 
  ArrowLeft, 
  Calendar, 
  DollarSign, 
  MapPin, 
  Users, 
  CheckCircle2,
  Clock,
  AlertCircle
} from "lucide-react";
import { Link } from "wouter";

export default function PackageDetails() {
  const [, params] = useRoute("/package/:id");
  const [, setLocation] = useLocation();
  const packageId = params?.id ? parseInt(params.id) : null;

  const { data: packages = [], isLoading, error } = useQuery({
    queryKey: ['packages'],
    queryFn: () => getPackages(),
  });

  if (isLoading) {
    return (
      <div className="min-h-screen bg-muted/30 py-12">
        <div className="container mx-auto px-4">
          <Skeleton className="h-8 w-32 mb-8" />
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2 space-y-6">
              <Skeleton className="h-96 w-full rounded-xl" />
              <Skeleton className="h-48 w-full" />
            </div>
            <div>
              <Skeleton className="h-64 w-full" />
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (error || !packageId) {
    return (
      <div className="min-h-screen bg-muted/30 py-12">
        <div className="container mx-auto px-4">
          <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>
              Failed to load package details. Please try again.
              <Button 
                variant="outline" 
                size="sm" 
                className="ml-4"
                onClick={() => setLocation('/')}
              >
                Go Home
              </Button>
            </AlertDescription>
          </Alert>
        </div>
      </div>
    );
  }

  const pkg = packages.find(p => p.id === packageId);

  if (!pkg) {
    return (
      <div className="min-h-screen bg-muted/30 py-12">
        <div className="container mx-auto px-4 text-center">
          <div className="max-w-md mx-auto">
            <div className="text-6xl mb-4">📦</div>
            <h2 className="text-2xl font-bold mb-2">Package Not Found</h2>
            <p className="text-muted-foreground mb-6">
              The package you're looking for doesn't exist or has been removed.
            </p>
            <Button onClick={() => setLocation('/')}>
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back to Home
            </Button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-muted/30">
      {/* Header */}
      <div className="bg-primary text-white py-8">
        <div className="container mx-auto px-4">
          <Button 
            variant="ghost" 
            size="sm"
            className="text-white hover:bg-white/10 mb-4"
            onClick={() => setLocation('/')}
          >
            <ArrowLeft className="w-4 h-4 mr-2" />
            Back to Home
          </Button>
          <div className="flex items-start justify-between">
            <div>
              <Badge className="mb-3 bg-secondary">
                {pkg.category.toUpperCase()}
              </Badge>
              <h1 className="text-3xl md:text-4xl font-heading font-bold mb-2">
                {pkg.title_en}
              </h1>
            </div>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Image */}
            {pkg.image && (
              <Card className="overflow-hidden shadow-lg">
                <img 
                  src={pkg.image} 
                  alt={pkg.title_en}
                  className="w-full h-96 object-cover"
                />
              </Card>
            )}

            {/* Description */}
            <Card className="shadow-lg">
              <CardContent className="p-8">
                <h2 className="text-2xl font-bold mb-4 text-primary">
                  Package Overview
                </h2>
                <p className="text-muted-foreground leading-relaxed text-lg">
                  {pkg.description_en}
                </p>
              </CardContent>
            </Card>

            {/* Features */}
            <Card className="shadow-lg">
              <CardContent className="p-8">
                <h2 className="text-2xl font-bold mb-6 text-primary">
                  What's Included
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {[
                    'Round-trip flights',
                    'Hotel accommodation',
                    'Daily breakfast',
                    'Airport transfers',
                    'Tour guide services',
                    'Travel insurance',
                    '24/7 support',
                    'All taxes included'
                  ].map((feature, index) => (
                    <div key={index} className="flex items-start gap-3">
                      <CheckCircle2 className="w-5 h-5 text-green-500 mt-0.5" />
                      <span>{feature}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Pricing Card */}
            <Card className="shadow-xl border-t-4 border-t-secondary sticky top-24">
              <CardContent className="p-6">
                <div className="text-center mb-6">
                  <p className="text-sm text-muted-foreground mb-2">Starting from</p>
                  <div className="text-4xl font-bold text-primary mb-1">
                    ${pkg.price}
                  </div>
                  <p className="text-sm text-muted-foreground">per person</p>
                </div>

                <div className="space-y-4 mb-6">
                  {pkg.duration && (
                    <div className="flex items-center gap-3 text-sm">
                      <Clock className="w-5 h-5 text-secondary" />
                      <div>
                        <span className="font-semibold">Duration:</span>
                        <span className="ml-2">{pkg.duration}</span>
                      </div>
                    </div>
                  )}
                  
                  <div className="flex items-center gap-3 text-sm">
                    <Users className="w-5 h-5 text-secondary" />
                    <div>
                      <span className="font-semibold">Group Size:</span>
                      <span className="ml-2">Min 2 persons</span>
                    </div>
                  </div>

                  <div className="flex items-center gap-3 text-sm">
                    <Calendar className="w-5 h-5 text-secondary" />
                    <div>
                      <span className="font-semibold">Available:</span>
                      <span className="ml-2">Year-round</span>
                    </div>
                  </div>
                </div>

                <Link href="/contact">
                  <Button 
                    size="lg" 
                    className="w-full bg-secondary hover:bg-secondary/90 text-white font-bold"
                  >
                    Inquire Now
                  </Button>
                </Link>

                <p className="text-xs text-center text-muted-foreground mt-4">
                  Free cancellation up to 48 hours before departure
                </p>
              </CardContent>
            </Card>

            {/* Contact Card */}
            <Card className="shadow-lg">
              <CardContent className="p-6">
                <h3 className="font-bold mb-4 text-primary">Need Help?</h3>
                <p className="text-sm text-muted-foreground mb-4">
                  Contact our travel experts for personalized assistance
                </p>
                <div className="space-y-2 text-sm">
                  <div className="flex items-center gap-2">
                    <span className="font-semibold">Phone:</span>
                    <a href="tel:0777317589" className="text-secondary hover:underline">
                      077 731 7589
                    </a>
                  </div>
                  <div className="flex items-center gap-2">
                    <span className="font-semibold">Email:</span>
                    <a href="mailto:info@sinathtravels.com" className="text-secondary hover:underline">
                      info@sinathtravels.com
                    </a>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
}