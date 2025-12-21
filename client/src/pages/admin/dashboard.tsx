import { useQuery } from "@tanstack/react-query";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Package, Mail, TrendingUp, AlertCircle } from "lucide-react";

export default function AdminDashboard() {
  const { data, isLoading, error, refetch } = useQuery({
    queryKey: ['admin-dashboard'],
    queryFn: async () => {
      const API_BASE = import.meta.env.VITE_API_URL?.replace('/api', '/api/admin') || 
                       'http://localhost:8080/server/api/admin';
      
      const response = await fetch(`${API_BASE}/dashboard.php`, {
        credentials: 'include',
      });

      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const result = await response.json();
      return result.data;
    },
  });

  if (isLoading) {
    return (
      <div className="space-y-8">
        <h1 className="text-3xl font-bold">Dashboard</h1>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          {[1,2,3,4].map(i => (
            <Card key={i}><CardContent className="p-6">
              <Skeleton className="h-20 w-full" />
            </CardContent></Card>
          ))}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <Alert variant="destructive">
        <AlertCircle className="h-4 w-4" />
        <AlertDescription>
          {error.toString()}
          <button onClick={() => refetch()} className="ml-4 px-3 py-1 bg-primary text-white rounded">
            Retry
          </button>
        </AlertDescription>
      </Alert>
    );
  }

  const p = data?.packages || {};
  const i = data?.inquiries || {};

  return (
    <div className="space-y-8">
      <h1 className="text-3xl font-bold">Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <StatCard title="Total Packages" value={p.total_packages || 0} 
          icon={<Package className="w-8 h-8" />} color="blue" />
        <StatCard title="Active Packages" value={p.active_packages || 0} 
          icon={<TrendingUp className="w-8 h-8" />} color="green" />
        <StatCard title="Total Inquiries" value={i.total_inquiries || 0} 
          icon={<Mail className="w-8 h-8" />} color="purple" />
        <StatCard title="New Inquiries" value={i.new_inquiries || 0} 
          icon={<Mail className="w-8 h-8" />} color="orange" highlight />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader><CardTitle>Package Breakdown</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <Row label="Tour Packages" value={p.tour_packages || 0} />
            <Row label="Visa Services" value={p.visa_packages || 0} />
            <Row label="Flight Tickets" value={p.ticket_packages || 0} />
            <Row label="Special Offers" value={p.offer_packages || 0} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader><CardTitle>Inquiry Status</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <Row label="New" value={i.new_inquiries || 0} color="orange" />
            <Row label="Read" value={i.read_inquiries || 0} color="blue" />
            <Row label="Replied" value={i.replied_inquiries || 0} color="green" />
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

function StatCard({ title, value, icon, color, highlight }: any) {
  const colors = {
    blue: 'bg-blue-500/10 text-blue-500',
    green: 'bg-green-500/10 text-green-500',
    purple: 'bg-purple-500/10 text-purple-500',
    orange: 'bg-orange-500/10 text-orange-500',
  };
  
  return (
    <Card className={highlight ? "border-orange-500 border-2" : ""}>
      <CardContent className="p-6">
        <div className="flex justify-between items-start">
          <div>
            <p className="text-sm text-muted-foreground mb-2">{title}</p>
            <p className="text-3xl font-bold">{value}</p>
          </div>
          <div className={`p-3 rounded-lg ${colors[color as keyof typeof colors]}`}>
            {icon}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

function Row({ label, value, color }: any) {
  const colors = {
    orange: 'text-orange-600',
    blue: 'text-blue-600',
    green: 'text-green-600',
  };
  
  return (
    <div className="flex justify-between items-center pb-3 border-b last:border-0">
      <span className="text-sm text-muted-foreground">{label}</span>
      <span className={`font-bold ${color ? colors[color as keyof typeof colors] : ''}`}>
        {value}
      </span>
    </div>
  );
}